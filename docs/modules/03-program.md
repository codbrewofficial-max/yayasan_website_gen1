# Modul 03 — Program

## Tujuan
Representasi **kegiatan/inisiatif** yayasan secara umum dan menjadi **induk opsional** dari Campaign. Contoh: "Program Beasiswa Anak Yatim", "Program Tanggap Bencana", "Program Pembangunan Masjid".

## Posisi dalam Hierarki

```
Program (induk kegiatan)
   └── Campaign (penggalangan dana spesifik di bawah program)
          └── Donation (transaksi donasi ke campaign tertentu)
```

## Keputusan Kunci

- **Program boleh 0, 1, atau banyak Campaign** — relasi opsional (`hasMany` tanpa constraint wajib minimal 1). Program tanpa campaign berfungsi murni informatif seperti Article.
- **Boleh banyak campaign paralel** dalam satu Program (misal "Batch Semester 1" dan "Dana Darurat Beasiswa" berjalan bersamaan).

## Struktur Data Inti

| Field | Keterangan |
|---|---|
| `title`, `slug` | SEO-friendly, unik per tenant |
| `content` | Rich text/block editor — deskripsi lengkap program |
| `featured_image` | Nyambung ke Media Library |
| `category` | Kategori program (Pendidikan, Kesehatan, Bencana, Sosial, dsb) |
| `status` | `ongoing`, `completed`, `upcoming` |
| `lokasi` *(opsional)* | Jika program spesifik ke daerah tertentu |
| `meta_title`, `meta_description`, `og_image` | SEO metadata |
| `author_id` | Siapa yang publish (Admin/Staff dengan permission) |
| `published_at` | Kontrol jadwal tayang |

## SEO Friendly

- **Structured data (JSON-LD)** — schema `Article`, bisa dikombinasikan dengan schema `NGO`/`Event` jika relevan.
- **Slug unik per tenant** (bukan global) — bisa dipakai ulang di yayasan lain tanpa konflik.
- **Sitemap otomatis** — Program published masuk `sitemap.xml` public site yayasan.
- **Breadcrumb** — `Beranda > Program > Nama Program`.

## Perilaku di Public Site

| Kondisi | Tampilan |
|---|---|
| Program tanpa Campaign | Seperti Article biasa (konten, galeri kegiatan, tanpa tombol donasi/progress bar) |
| Program dengan Campaign | Konten + section daftar campaign aktif (card per campaign: progress bar, tombol "Donasi Sekarang") |
| Banyak Campaign paralel | Sorting/filter (campaign berjalan ditampilkan duluan; yang selesai diberi badge "Selesai") |

## Data Model — Relasi ke Campaign

- `campaigns.program_id` **wajib** (`not null`) — campaign selalu di bawah program.
- Dari sisi Program, tidak ada constraint "harus punya campaign".
- Rollup statistik level Program (total dana dari semua campaign di bawahnya) di-agregasi real-time atau di-cache.

## Keterkaitan
- [Modul 04 — Campaign](./04-campaign.md): penggalangan dana spesifik di bawah program.
- [Modul 10 — Media Library](./10-media-library.md): featured image & galeri.
- [Modul 06 — Article](./06-article.md): pola konten SEO-friendly yang sama.