# ERD — Modul 03: Program

## Tabel: `programs`

Induk kegiatan yayasan, SEO-friendly, induk opsional dari Campaign.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `title` | string | | Judul program |
| `slug` | string | UQ (per tenant) | SEO-friendly |
| `content` | longText | | Rich text / block editor |
| `featured_image_id` | uuid/ulid | FK → media (nullable) | Thumbnail |
| `category` | string | | Pendidikan, Kesehatan, Bencana, Sosial, dsb |
| `status` | enum | | `ongoing` / `completed` / `upcoming` |
| `location` | string | | Opsional, jika spesifik daerah |
| `meta_title` | string | | SEO |
| `meta_description` | text | | SEO |
| `og_image_id` | uuid/ulid | FK → media (nullable) | OG image |
| `author_id` | uuid/ulid | FK → users | Yang publish |
| `published_at` | timestamp | | Jadwal tayang |
| `views_count` | unsignedInteger | | Counter (cache) |
| `sort_order` | unsignedInteger | | Urutan tampil |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Relasi

```
programs.tenant_id ───────────> tenants.id
programs.featured_image_id ───> media.id (nullable)
programs.og_image_id ─────────> media.id (nullable)
programs.author_id ───────────> users.id
programs 1──N  campaigns       (campaigns.program_id wajib)
```

## Catatan
- **0, 1, atau banyak Campaign** — relasi `hasMany` opsional (boleh tidak punya).
- **Banyak campaign paralel** diperbolehkan.
- Rollup total dana program di-agregasi dari campaigns (real-time/cache).

## Keterkaitan
- [Modul 04 — Campaign](./04-campaign.md): campaign.program_id wajib.
- [Modul 10 — Media Library](./10-media-library.md): thumbnail.
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): views_count cache.