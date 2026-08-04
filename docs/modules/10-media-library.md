# Modul 10 — Media Library

## Tujuan
**Bank pusat aset** platform — tempat semua modul (Program, Article, Campaign, Donation, Album Gallery, Gallery, Pengurus, User) mengambil thumbnail/foto/dokumen. Semua modul menyimpan `media_id`, bukan path string → jika file diganti, semua halaman otomatis ter-update.

## Jenis Aset & Aturannya

| Jenis | Format | Aturan Upload | Hasil Akhir |
|---|---|---|---|
| **Dokumen** | PDF, Word, PPTX, Excel | Max **10 MB** | Disimpan asli, tidak diubah |
| **Gambar** | jpg, jpeg, png, webp | Max **10 MB** (original) | Auto-convert ke **webp** + resize, target **< 100 KB** |

## Keputusan Kunci
- **Buang file asli** — setelah diproses jadi webp, file asli dihapus (hemat storage).
- **Beberapa varian ukuran** — auto-generate `thumbnail`, `medium`, `large` (semua webp).
- **Folder + filter + search** — library terorganisir (folder/kategori, filter tipe file, filter modul pemakai).

## Pipeline Gambar

```
Upload (jpg/jpeg/png/webp, max 10MB)
→ convert ke webp
→ generate varian: thumbnail / medium / large (target < 100KB untuk yang dipakai di web)
→ buang file asli
→ simpan path tiap varian
```

## Struktur Data Inti (`media`)

| Field | Keterangan |
|---|---|
| `tenant_id` | Scoping per yayasan |
| `type` | `image` / `document` |
| `original_name`, `mime_type`, `file_size` | Metadata asli |
| `path_thumbnail`, `path_medium`, `path_large` | Varian gambar webp |
| `path` | Lokasi file dokumen (untuk type `document`) |
| `width`, `height` | Dimensi |
| `title`, `alt_text` | Label + alt text (SEO/aksesibilitas) |
| `category` | Folder/kategori |
| `created_by` | User yang upload |
| `usage` | Relasi: modul + konten yang memakai file ini |

## Perilaku di Admin & Public Site
- Admin: grid/folder view dengan filter (tipe, kategori, modul pemakai) + search.
- Semua modul mereferensikan via `media_id` → penggantian file otomatis tersebar ke semua pemakaian.
- Alt text disarankan (SEO/aksesibilitas).

## Keterkaitan
Dipakai oleh hampir semua modul: [Program](./03-program.md), [Campaign](./04-campaign.md), [Article](./06-article.md), [Album Gallery](./07-album-gallery.md), [Gallery](./08-gallery.md), [Pengurus](./09-pengurus.md), [User](./01-auth.md).