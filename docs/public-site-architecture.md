# Public Site Architecture — Templated Renderer

Dokumen arsitektur public website berdasarkan keputusan diskusi:
- Template = fixed (struktur/layout), dibuat Super Admin
- Content = data terstruktur, diedit Admin Yayasan (bukan HTML)
- Yayasan tidak bisa buat halaman baru sendiri (menambah halaman = permintaan custom)
- Template baru on-demand (template #1 = blueprint)

---

## Ringkasan Keputusan

| Aspek | Keputusan |
|---|---|
| Lapisan | Renderer inti (sekali) + template layer (berulang) |
| Template | Fixed — struktur/layout/gaya; bukan diedit yayasan |
| Content | Data terstruktur (content sections), diedit via editor |
| Halaman baru | Tidak bisa dibuat yayasan — permintaan custom ke Super Admin |
| Interaktivitas | Blade murni + Livewire component (pagination/filter/form) |
| Shared | `_shared/` blade components reusable |
| Template baru | On-demand; template #1 = blueprint/pola |

---

## 1. Dua Lapisan

```
RENDERER INTI (dibangun sekali)          TEMPLATE LAYER (dibuat berulang)
├─ resolve tenant (Host header)          ├─ layout + view per halaman
├─ routing halaman fixed                 ├─ config_schema (warna, font, section)
├─ data loading (Service per halaman)    └─ render section content
├─ SEO injection (meta, JSON-LD)
├─ content sections dari DB
└─ shared blade components (_shared/)
```

**Aturan kunci**: template layer **tidak boleh** berisi logic data (query, scope, SEO). Semua data disiapkan renderer inti → template tinggal render.

## 2. Routing & Daftar Halaman Final

Rute public site **fixed** dan sama di semua template (hanya render yang beda). Berikut daftar halaman final hasil keputusan:

### Beranda & Global
| Halaman | Route |
|---|---|
| **Beranda** (campaign unggulan, berita terbaru, program) | `/` |
| **Tombol Donasi Global** | di header & footer — semua halaman |

### Modul (terikat data)
| Halaman | Route |
|---|---|
| List + Detail Program | `/programs`, `/program/{slug}` |
| List + Detail Campaign | `/campaigns`, `/campaign/{slug}` |
| Donasi per Campaign | `/donasi/{campaign}` |
| List + Detail Article | `/articles`, `/article/{slug}` |
| List + Detail Album (galeri) | `/albums`, `/album/{slug}` |
| Struktur Pengurus | `/pengurus` |
| Halaman Donasi (cara berdonasi + alur) | `/donasi` |
| Form Kontak / Leads | `/kontak` |

### Halaman Statis (diedit Admin Yayasan via content sections)
| Halaman | Route |
|---|---|
| Tentang Kami (termasuk Visi & Misi — digabung) | `/tentang` |
| FAQ | `/faq` |
| Kebijakan Privasi | `/privasi` |
| Syarat & Ketentuan | `/ketentuan` |

> **Keputusan penggabungan**: Visi & Misi digabung ke dalam Tentang Kami. Privasi & Ketentuan tetap terpisah.

- Halaman statis dibuat **Super Admin** (fixed); isinya diedit Admin Yayasan lewat content sections.
- URL halaman statis didaftarkan di rute renderer inti.
- Daftar bisa bertambah via **permintaan custom** (bukan dibuat yayasan sendiri).

- Route → controller renderer inti → `TemplateService` memilih view sesuai tenant.
- Slug **per tenant** (di-resolve dengan scope tenant aktif).

## 3. Pemilihan Template

```
TemplateService
  ├─ ambil template_id dari website_configs (cached)
  ├─ pilih view: templates/{slug}/...
  └─ render config (warna/font/section dari config_schema)
```

- Tiap template punya `config_schema` (slot customisasi: warna, font, posisi section).
- Cache pilihan template per tenant (hindari query tiap request).

## 4. Data Loading (Renderer Inti)

Setiap halaman punya method controller/service yang menyiapkan data **lengkap** sebelum render:

```
PageData: home → campaigns aktif, berita terbaru, program unggulan, content sections
          campaign detail → campaign, progress, donasi terbaru (anonim-aware)
          article detail  → artikel, related
          album detail    → items gallery
          pengurus        → members by group
```

- `views_count` di-increment di renderer inti (bukan template).
- SEO dibangun renderer inti.

## 5. SEO Injection (terpusat)

```
RendererController
  ├─ bangun meta title, description, canonical
  ├─ Open Graph / Twitter Card
  ├─ JSON-LD (Article, ImageGallery, dsb)
  └─ pass $seo ke template → render di <head>
```

Template tidak perlu tahu aturan SEO — konsisten antar template.

## 6. Media Output

- Renderer menyediakan path varian (thumbnail/medium/large) + `alt_text` dari `media`.
- Template pakai `$image->url('thumbnail')` — tidak perlu tahu pipeline.

## 7. Editing Konten (Content Sections)

`website_configs` diperluas dengan **content sections per halaman**:

| Field | Keterangan |
|---|---|
| `page` | Halaman mana (home, program, kontak, dsb) |
| `section_key` | Identitas section (hero, tentang, campaign-unggulan, dll) |
| `content` | JSON: teks, gambar (media_id), pilihan konten |
| `enabled` | Tampil / sembunyi |
| `sort_order` | Urutan tampil |
| `updated_at` | Waktu update |

- Yayasan edit isi section lewat **editor terstruktur** (teks, pilih gambar, pilih campaign/program/artikel, toggle, urutan).
- **Bukan HTML bebas** — template tetap fixed & aman.

## 8. Halaman Statis Custom

- Yayasan **tidak bisa** membuat halaman baru sendiri.
- Halaman-halaman **fixed** dibuat oleh Super Admin.
- Jika yayasan ingin halaman tambahan → **permintaan custom** ke Super Admin (ditambahkan ke customisasi).

## 9. Struktur Folder Template

```
resources/views/
├── templates/
│   ├── _shared/              # komponen reusable semua template
│   │   ├── header.blade.php
│   │   ├── footer.blade.php
│   │   ├── campaign-card.blade.php
│   │   └── seo-head.blade.php
│   └── template-one/         # template #1 (blueprint)
│       ├── layout.blade.php
│       ├── home.blade.php
│       ├── campaign.blade.php
│       ├── article.blade.php
│       └── ...
```

- `_shared/` dibangun sekali, dipakai ulang semua template.
- Template baru = copy struktur template-one → ubah tampilan → daftarkan di `templates`.

## 10. Interaktivitas

| Pendekatan | Pemakaian |
|---|---|
| **Blade murni** | Halaman statis (mayoritas) — ringan, mudah di-edit |
| **Livewire component** | Interaksi: pagination, filter campaign, form kontak |

Rekomendasi: template default pakai **Blade murni + Livewire component** untuk bagian interaktif — bukan Livewire penuh di semua halaman.

---

## Keterkaitan Dokumen Lain
- [Backend Architecture](./backend-architecture.md)
- [Infrastruktur & Stack](./infrastructure.md)
- [Roadmap MVP](./roadmap.md)
- [Dokumentasi Modul](../modules/index.md)