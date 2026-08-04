# UI/UX Wireframe — Public Website

Wireframe public site (ASCII + deskripsi) + pemetaan **content sections** per halaman. Menjadi panduan pembuatan template #1 dan dasar struktur `website_configs` (editor yayasan).

## Konvensi Wireframe

- `[ ]` = area/section berisi konten
- Label di dalam blok = isi/fungsi section
- Konvensi content section: setiap blok bertanda `◆` adalah **section editable yayasan** (hero, tentang, dll) → masuk `website_configs`.
- Baris dengan `#` = penyempurnaan global (header/footer/donasi).

### Layout Global (semua halaman)

```
┌──────────────────────────────────────────────────────┐
│ HEADER [logo] [menu nav] [CTA Donasi Global] [hamb] │  ◆ header
├──────────────────────────────────────────────────────┤
│            <KONTEN HALAMAN (varies)>                │
├──────────────────────────────────────────────────────┤
│ FOOTER [about] [links] [sosmed] [donasi] [kontak]    │
└──────────────────────────────────────────────────────┘
```

- **Header**: logo, nav menu, tombol "Donasi" (global, arah ke `/donasi`).
- **Footer**: info, tautan, sosmed, CTA donasi lagi.
- Unsur header/footer = komponen `_shared/`.

---

## 1. Beranda (`/`)

```
┌──────────────────────────────────────────────┐
│ ◆ Section: HERO                               │  ◆
│   [judul besar, subjudul, CTA Donasi]         │  section: hero
│   [background image]                          │
├──────────────────────────────────────────────┤
│ ◆ Section: TENTANG (ringkas)                  │  section: tentang
│   [teks singkat, tombol "Selengkapnya"]       │
├──────────────────────────────────────────────┤
│ ◆ Section: CAMPAIGN UNGGULAN                  │  section: featured_campaigns
│   [grid card campaign aktif + progress]       │
├──────────────────────────────────────────────┤
│ ◆ Section: PROGRAM                           │  section: programs
│   [card program terpilih]                     │
├──────────────────────────────────────────────┤
│ ◆ Section: BERITA TERBARU                    │  section: latest_articles
│   [3 kartu berita]                           │
├──────────────────────────────────────────────┤
│ ◆ Section: STATISTIK                         │  section: stats
│   [total donasi, jumlah donatur, campaign]    │
├──────────────────────────────────────────────┤
│ ◆ Section: CTA MEDONASI (opsional)            │  section: cta
│   [ajakan donasi + tombol]                    │
└──────────────────────────────────────────────┘
```

**Content sections**: hero, tentang, featured_campaigns, programs, latest_articles, stats, cta.

## 2. List Campaign (`/campaigns`)

```
┌──────────────────────────────────────────────┐
│ Page-heading [judul, deskripsi]                  │
├──────────────────────────────────────────────┤
│ [Filter kategori / search]   (Livewire)       │
├──────────────────────────────────────────────┤
│ [Card campaign × N]                          │
│   tiap card: image, judul, progress, tombol   │
│   "Donasi"                                    │
├──────────────────────────────────────────────┤
│ [Pagination]                                 │
└──────────────────────────────────────────────┘
```

- Halaman ini **otomatis** dari data `campaigns` (bukan editable section; hanya heading simpl).
- Filter/pagination = Livewire component.

## 3. Detail Campaign (`/campaign/{slug}`) — paling penting

```
┌──────────────────────────────────────────────┐
│ [Breadcrumb]                                    │
├──────────────────────────────────────────────┤
│ [Image utama camp] │ [Informasi:           │
│                      - judul                 │
│                      - progress (target/terkumpul)│
│                      - sisa waktu            │
│                      - [Form Donasi CTA]]    │
├──────────────────────────────────────────────┤
│ ◆ Section: CERITA / deskripsi campaign        │  section: campaign_story
│   [rich text]                                │
├──────────────────────────────────────────────┤
│ ◆ Section: DONATUR TERBARU (jika show)     │  section: recent_donors
│   [daftar nama + jumlah (anonim-aware)]       │
├──────────────────────────────────────────────┤
│ ◆ Section: PROGRAM TERKAIT                   │  section: related_program
│   [link ke program induk]                    │
├──────────────────────────────────────────────┤
│ ◆ Section: CAMPAIGN LAIN /TERKAT (opsional)  │  section: related_campaigns
│   [card campaign lain]                       │
└──────────────────────────────────────────────┘
```

- **CTA Donasi** dominan → form ringkas di sisi info (nominal, nama, pilih bayar).
- Konversi donasi jadi fokus utama.

## 4. Flow Donasi (`/donasi` dari `/donasi/{campaign}`)

```
┌──────────────────────────────────────────────┐
│ [Judul "Donasi"] / per campaign              │
├──────────────────────────────────────────────┤
│ Step 1: PILIH NOMINAL                        │
│   [tombol nominal cepat][input custom]       │
├──────────────────────────────────────────────┤
│ Step 2: DATA DIRI                            │
│   [nama, email, HP, pesan, anonim checkbox]  │
├──────────────────────────────────────────────┤
│ Step 3: METODE PEMBAYARAN                    │
│   [VA, QRIS, e-wallet, kartu]                │
├──────────────────────────────────────────────┤
│ Step 4: [RINGKASAN + tombol Bayar]           │
├──────────────────────────────────────────────┤
│ → Alihkan ke Midtrans Snap → sukses page      │
└──────────────────────────────────────────────┘
```

- **Halaman sukses**: konfirmasi, nominal, referensi, tombol lihat detail/riwayat (jika login). 
- Ini alur krusial → diprioritaskan wireframe & pengujian.

## 4. List & Detail Program (`/programs`, `/program/{slug}`)

```
List:  [heading] [card program × N] [pagination]

Detail Program:
┌──────────────────────────────────────────────┐
│ ◆ Section: HEADER PROGRAM                     │  section: program_hero
│   [image, judul, status, lokasi]              │
├──────────────────────────────────────────────┤
│ ◆ Section: DESKRIPSI                          │  section: program_story
│   [rich text]                                │
├──────────────────────────────────────────────┤
│ Section: CAMPAIGN PROGRAM (dinamis)           │
│   [campaign aktif terkait + progress]        │   * otomatis dari data
├──────────────────────────────────────────────┤
│ Section: GALERI KEGIATAN (opsional)          │
│   [foto terkait]                             │
└──────────────────────────────────────────────┘
```

- Like campaign story, program punya `section: program_story` editable.

## 5. Article (`/articles`, `/article/{slug}`)

```
List:
  [heading] [filter kategori] [card artikel × N]

Detail:
┌──────────────────────────────────────────────┐
│ [judul, meta (tanggal, penulis, reading time)]│
├──────────────────────────────────────────────┤
│ [image utama + content artikel]              │
├──────────────────────────────────────────────┤
│ [Social share: WA/FB/X/copy link]            │
├──────────────────────────────────────────────┤
│ [Related articles (by kategori/tag)]         │
└──────────────────────────────────────────────┘
```

- Artikel = konten modul (bukan content section editable; heading/list/detail terikon dari data).

## 6. Galeri (`/albums`, `/album/{slug}`)

```
List:
[heading] [card album: cover + judul + jumlah foto]

Detail album:
┌──────────────────────────────────────────────┐
│ [judul album, deskripsi, meta]                 │
├──────────────────────────────────────────────┤
│ [Grid foto × N]  (lightbox)                  │
└──────────────────────────────────────────────┘
```

## 7. Struktur Pengurus (`/pengurus`)

```
┌──────────────────────────────────────────────┐
│ Page-heading                                   │
├──────────────────────────────────────────────┤
│ ◆ Section: PEMBINA                            │  group: pembina
│   [grid kartu foto-nama-jabatan]              │
├──────────────────────────────────────────────┤
│ ◆ Section: PENGAWAS                           │  group: pengawas
├──────────────────────────────────────────────┤
│ ◆ Section: PENGURUS INTI                      │  group: pengurus_inti
├──────────────────────────────────────────────┤
│ ◆ Section: ANGGOTA                            │  group: anggota
└──────────────────────────────────────────────┘
```

- Tiap group jabatan = konten dari modul pengurus (data, bukan editable section).

## 8. Kontak / Leads (`/kontak`)

```
┌──────────────────────────────────────────────┐
│ Page-heading        │ [Form kontak]           │
│ [alamat, telp,      │   nama, email/HP, topik,│
│  email, maps]       │   pesan                │
│                     │   [Hubungi Email]      │
│                     │   [Hubungi WhatsApp]   │
└──────────────────────────────────────────────┘
```

- Tombol email → backend kirim ke yayasan. WhatsApp → redirect wa.me.
- Content sections: **kontak info** (alamat/highlight).

## 9. Halaman Statis (Tentang/FAQ/Privasi/Ketentuan)

```
┌──────────────────────────────────────────────┐
│ Page-heading / judul                          │
├──────────────────────────────────────────────┤
│ ◆ Section: KONTEN (prose)                    │  section: static_content
│   [teks, gambar (Visi-Misi, dsb)]            │
├──────────────────────────────────────────────┤
│ ◆ Section: (FAQ → akordeon list)              │  section: faq
└──────────────────────────────────────────────┘
```

- Halaman statis = 1+ content sections (konten utama), isinya diedit yayasan.

---

## Ringkasan Content Sections (dasar `website_configs`)

| Halaman | Section editable |
|---|---|
| Beranda | hero, tentang, featured_campaigns, programs, latest_articles, stats, cta |
| Campaign | campaign_story, recent_donors, related_program, related_campaigns |
| Program | program_hero, program_story |
| Kontak | kontak_info |
| Statis | static_content, faq |

Section non-editable (data-driven): campaign list, article, galeri, pengurus.

## Catatan Implementasi
- Unsur global (header/footer) & CTA donasi → komponen `_shared/`.
- Alur donasi adalah halaman dengan konversi tertinggi → prioritas pengujian UX.
- Wireframe ini adalah **low-mid fidelity**; pal ruang & gaya disempurnakan saat template #1 dibangun (high-fidelity).

---

## Keterkaitan Dokumen Lain
- [Public Site Architecture](./public-site-architecture.md)
- [Dokumentasi Modul](../modules/index.md)