# ERD — Modul 07: Album Gallery

## Tabel: `albums`

Induk koleksi foto kegiatan/arsip, SEO-friendly, induk opsional dari Gallery.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `title` | string | | Judul album |
| `slug` | string | UQ (per tenant) | SEO-friendly |
| `description` | string | | Deskripsi singkat |
| `featured_image_id` | uuid/ulid | FK → media (nullable) | Cover |
| `category` | string | | Tahunan, Kegiatan, Acara |
| `status` | enum | | `draft` / `scheduled` / `published` |
| `published_at` | timestamp | | Jadwal tayang |
| `author_id` | uuid/ulid | FK → users | |
| `views_count` | integer | | Counter (cache) |
| `meta_title` | string | | SEO |
| `meta_description` | text | | SEO |
| `sort_order` | unsignedInteger | | Urutan tampil |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Relasi

```
albums.tenant_id ───────────> tenants.id
albums.featured_image_id ───> media.id (nullable)
albums.author_id ───────────> users.id
albums 1──N  galleries       (galleries.album_id wajib)
```

## Catatan
- **0, 1, atau banyak Gallery** — `hasMany` opsional (album boleh kosong).
- Standalone — tanpa relasi ke program.

## Keterkaitan
- [Modul 08 — Gallery](./08-gallery.md): item anak album.
- [Modul 10 — Media Library](./10-media-library.md): cover.