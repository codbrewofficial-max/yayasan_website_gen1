# ERD — Modul 10: Media Library

## Tabel: `media`

Bank pusat aset platform (gambar webp + dokumen). Direferensikan via **FK langsung** dari tabel pemakai (tanpa pivot).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `type` | enum | | `image` / `document` |
| `original_name` | string | | Nama file asli |
| `mime_type` | string | | jpg/png/webp/pdf/docx/xlsx/ppt |
| `file_size` | bigint | | Ukuran original (bytes) |
| `path_thumbnail` | string | | Varian webp kecil |
| `path_medium` | string | | Varian webp sedang |
| `path_large` | string | | Varian webp besar |
| `path` | string | | Lokasi file (untuk type `document`) |
| `width` | int | | Dimensi (gambar) |
| `height` | int | | Dimensi (gambar) |
| `title` | string | | Label |
| `alt_text` | string | | Alt text (SEO/aksesibilitas) |
| `category` | string | | Folder/kategori |
| `created_by` | uuid/ulid | FK → users | |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Pipeline Gambar

```
Upload (jpg/jpeg/png/webp, max 10MB)
→ convert webp
→ generate varian thumbnail / medium / large (target <100KB di web)
→ buang file asli
→ simpan path tiap varian
```

## Relasi (FK langsung — tanpa pivot)

```
programs.featured_image_id ─┐
campaigns.featured_image_id ├──> media.id
articles.featured_image_id ─┤
albums.featured_image_id ───┤
galleries.image_id ──────────┘
members.photo_id ───────────> media.id
users.avatar_id ────────────> media.id
tenants.tenant_documents.media_id ──> media.id
```

## Catatan
- Tidak ada tabel `media_usage` — pemakaian dilacak via query balik FK pemakai.
- Ubah file → otomatis update di semua pemakai (karena referensi by `media_id`).

## Keterkaitan
- [Modul 08 — Gallery](./08-gallery.md): image wajib.
- [Modul 02 — Tenant](./02-tenant.md): dokumen legalitas.
- Banyak modul lain mereferensikan.