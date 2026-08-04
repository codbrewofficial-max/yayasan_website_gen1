# Modul 07 — Album Gallery

## Tujuan
Kontainer **induk** dari koleksi foto kegiatan/arsip yayasan. SEO-friendly dan ditampilkan di public site sebagai galeri dokumentasi.

## Posisi dalam Hierarki

```
Album Gallery (induk koleksi)
   └── Gallery (item foto + judul)
```

## Keputusan Kunci
- **Album boleh kosong** — boleh dibuat tanpa foto; relasi `hasMany` ke Gallery bersifat opsional.
- **Berdiri sendiri** — tanpa relasi ke Program; murni dokumentasi kegiatan/arsip.

## Struktur Data Inti (`albums`)

| Field | Keterangan |
|---|---|
| `title`, `slug` | SEO-friendly, unik per tenant |
| `description` | Deskripsi singkat album |
| `featured_image` | Nyambung Media Library |
| `category` | Kategori album (Tahunan, Kegiatan, Acara, dsb) |
| `status` | `draft`, `scheduled`, `published` |
| `published_at` | Jadwal tayang |
| `author_id` | Yang publish (Admin/Staff dengan permission) |
| `views_count` | Counter tampil album (cache) |

## SEO

- **Structured data (JSON-LD)** — schema `ImageGallery` + `Article` (galeri muncul di Google Images).
- **Breadcrumb** (`Beranda > Album > Nama Album`), sitemap otomatis.
- **Open Graph** — penting karena konten visual paling sering di-share.
- **Related albums** — suggest album lain sekategori untuk internal linking.

## Data Model — Item Foto

- `galleries.album_id` **wajib** (`not null`) — item selalu di bawah album.
- Semua gambar lewat pipeline Media Library (auto webp + resize).

## Perilaku di Public Site
- Halaman Album merender daftar item Gallery (grid foto + judul, lightbox/zoom).
- Album kosong → tampil placeholder (tetap bisa dipublish).

## Views & Insight
- `views_count` di sini adalah **cache**; data granular diambil dari `page_visits` di [Modul 13 — Tracking & Insight](./13-tracking-insight.md) via `page_url` berisi slug album.

## Keterkaitan
- [Modul 08 — Gallery](./08-gallery.md): item foto anak album.
- [Modul 10 — Media Library](./10-media-library.md): penyimpanan & pengolahan gambar.