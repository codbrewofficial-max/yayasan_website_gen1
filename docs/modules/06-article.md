# Modul 06 — Article

## Tujuan
Konten blog/berita yayasan yang **berdiri sendiri** (tanpa relasi ke Program) dan SEO-friendly.

## Keputusan Kunci
- **Tidak ada relasi ke Program** — artikel murni independen.
- **Tanpa fitur komentar** — cukup tombol share ke social media (menghindari beban moderasi spam/konten negatif).

## Struktur Data Inti

| Field | Keterangan |
|---|---|
| `title`, `slug` | SEO-friendly, unik per tenant |
| `content` | Rich text/block editor |
| `excerpt` | Ringkasan pendek — preview di listing & meta description default |
| `featured_image` | Nyambung Media Library |
| `category`, `tags` | Kategori (Berita, Kegiatan, Pengumuman) + tags bebas untuk filtering |
| `status` | `draft`, `scheduled`, `published` |
| `published_at` | Mendukung jadwal tayang (scheduled publish) |
| `author_id` | Penulis (Admin/Staff dengan permission `article.publish`) |
| `views_count` | Counter jumlah dibaca |
| `reading_time` | Computed otomatis dari jumlah kata konten |

## SEO

- **Structured data (JSON-LD)** — schema `Article`/`BlogPosting` (termasuk `datePublished`, `dateModified`, `author`).
- **Slug unik per tenant**, breadcrumb (`Beranda > Artikel > Judul`), sitemap otomatis.
- **Open Graph & Twitter Card** — penting karena artikel paling sering di-share.
- **Related articles** — suggest artikel lain berdasarkan kategori/tags yang sama (SEO internal linking & retensi pembaca).
- **Canonical URL** — penting jika artikel dipindah slug-nya.

## Perilaku di Public Site
- Listing artikel + halaman detail.
- Tombol share social media (WhatsApp, Facebook, Twitter/X, copy link).
- Related articles di halaman detail.

## Keterkaitan
- [Modul 03 — Program](./03-program.md): pola konten SEO-friendly yang sama (tanpa relasi).
- [Modul 10 — Media Library](./10-media-library.md): featured image.
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): `views_count` untuk insight konten.