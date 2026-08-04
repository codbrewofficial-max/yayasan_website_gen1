# Modul 08 — Gallery

## Tujuan
Item foto individual (anak dari Album Gallery) — hanya berisi foto beserta judulnya.

## Posisi dalam Hierarki

```
Album Gallery (induk koleksi)
   └── Gallery (item foto + judul)  ← modul ini
```

## Struktur Data Inti (`galleries`)

| Field | Keterangan |
|---|---|
| `album_id` | Wajib — foto milik Album mana |
| `title` / caption | Judul foto (ditampilkan di bawah foto) |
| `image` | File dari Media Library (auto webp + resize < 100KB) |
| `sort_order` | Urutan tampil dalam album |

## Perilaku di Public Site
- Saat pengunjung membuka sebuah Album, dirender daftar item Gallery: **grid foto + judul masing-masing**, bisa dengan lightbox/zoom.
- Album tampil kosong jika belum ada item.

## Pengelolaan di Admin
- Dikelola via halaman Album (tambah / hapus / urutkan foto).
- Foto diunggah lewat Media Library (Modul 10) — otomatis diproses ke webp.

## Catatan
- **Tanpa properti SEO sendiri** — SEO berada di level Album/Program/Article.
- Murni aset visual dengan metadata minimal (judul + urutan).

## Keterkaitan
- [Modul 07 — Album Gallery](./07-album-gallery.md): induk koleksi.
- [Modul 10 — Media Library](./10-media-library.md): penyimpanan & pengolahan gambar.