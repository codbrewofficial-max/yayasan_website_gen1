# ERD — Modul 08: Gallery

## Tabel: `galleries`

Item foto individual (anak Album Gallery) — foto + judul.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `album_id` | uuid/ulid | FK → albums (**wajib**) | Induk |
| `title` | string | | Judul/caption foto |
| `image_id` | uuid/ulid | FK → media (**wajib**) | Foto terolah webp |
| `sort_order` | unsignedInteger | | Urutan tampil dalam album |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Relasi

```
galleries.album_id ────> albums.id (wajib)
galleries.image_id ────> media.id  (wajib)
```

## Catatan
- Tanpa properti SEO sendiri.
- `sort_order` integer increment per album (urutan tampil).

## Keterkaitan
- [Modul 07 — Album Gallery](./07-album-gallery.md)
- [Modul 10 — Media Library](./10-media-library.md): image wajib dari media.