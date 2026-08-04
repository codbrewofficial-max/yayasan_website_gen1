# ERD — Modul 06: Article

## Tabel: `articles`

Konten blog/berita yayasan, berdiri sendiri (tanpa relasi program), SEO-friendly.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `title` | string | | Judul |
| `slug` | string | UQ (per tenant) | SEO-friendly |
| `content` | longText | | Rich text |
| `excerpt` | string | | Ringkasan pendek |
| `featured_image_id` | uuid/ulid | FK → media (nullable) | |
| `category` | string | | Berita, Kegiatan, Pengumuman |
| `tags` | array | | Tags bebas, filter |
| `status` | enum | | `draft` / `scheduled` / `published` |
| `published_at` | timestamp | | Jadwal tayang |
| `author_id` | uuid/ulid | FK → users | Penulis |
| `views_count` | unsignedInteger | | Counter (cache) |
| `reading_time` | smallint | | Computed dari kata |
| `meta_title` | string | | SEO |
| `meta_description` | text | | SEO |
| `og_image_id` | uuid/ulid | FK → media (nullable) | OG image |
| `canonical_url` | string | | Opsional |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Catatan
- **Berdiri sendiri** — tidak ada FK ke `programs` (keputusan).
- **Tanpa fitur komentar** — cukup tombol share.
- Related articles by kategori/tags (query, bukan tabel pivot).

## Relasi

```
articles.tenant_id ──────────> tenants.id
articles.featured_image_id ──> media.id (nullable)
articles.author_id ──────────> users.id
articles.og_image_id ────────> media.id (nullable)
```

## Keterkaitan
- [Modul 10 — Media Library](./10-media-library.md): featured/og image.
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): views_count cache.
- [Modul 06 — dokumentasi](./docs/../modules/06-article.md): perilaku SEO.