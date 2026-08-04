# ERD — Modul 12: Website Configuration

## Tabel: `website_configs`

Konfigurasi **operasional** website per tenant (visible ke Admin Yayasan + Super Admin).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `tenant_id` | uuid/ulid | PK+FK → tenants | 1:1 dengan tenant |
| `site_name` | string | | Nama situs |
| `tagline` | string | | |
| `logo_id` | uuid/ulid | FK → media | |
| `favicon_id` | uuid/ulid | FK → media | |
| `primary_color` | string | | Warna utama |
| `accent_color` | string | | Warna aksen |
| `font` | string | | Font pilihan |
| `template_id` | bigint | FK → templates | Template terpilih |
| `hero_title` | string | | |
| `hero_description` | string | | |
| `hero_background_id` | uuid/ulid | FK → media | |
| `homepage_sections` | json | | Urutan/tampilan section |
| `social_media` | json | | FB, IG, X, YT, WA |
| `address` | text | | |
| `contact_email` | string | | |
| `contact_phone` | string | | |
| `maps_embed` | string | | |
| `footer_text` | string | | |
| `footer_links` | json | | |
| `copyright` | string | | |
| `meta_title` | string | | SEO default |
| `meta_description` | string | | |
| `og_image_id` | uuid/ulid | FK → media | |
| `updated_at` | timestamp | | |

## Tabel: `technical_configs`

Konfigurasi **teknis** per tenant (hidden — hanya Super Admin).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `tenant_id` | uuid/ulid | PK+FK → tenants | 1:1 |
| `midtrans_server_key` | string | | |
| `midtrans_client_key` | string | | |
| `midtrans_is_production` | boolean | | Sandbox/production |
| `smtp_host` | string | | |
| `smtp_port` | int | | |
| `smtp_username` | string | | |
| `smtp_password` | string | | |
| `timezone` | string | | |
| `locale` | string | | |
| `currency` | string | | |
| `maintenance_mode` | boolean | | |
| `updated_at` | timestamp | | |

## Tabel: `templates`

Daftar template public site (platform, tanpa tenant_id).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | bigint | PK | Auto increment |
| `name` | string | | Nama template |
| `slug` | string | UQ | |
| `is_default` | boolean | | |
| `is_active` | boolean | | |
| `config_schema` | json | | Slot customisasi tema |
| `created_at`, `updated_at` | timestamp | | |

## Relasi

```
tenants 1──1 website_configs
tenants 1──1 technical_configs
website_configs.logo_id / favicon_id / hero_background_id ──> media.id
website_configs  N──1 templates
```

## Catatan Akses
- **Super Admin**: semua (operasional + teknis).
- **Admin Yayasan**: hanya operasional (`website_configs`), inputan teknis disembunyikan.

## Keterkaitan
- [Modul 02 — Tenant](./02-tenant.md)
- [Modul 05 — Donation](./05-donation.md): midtrans key dipakai di sini.
- [Modul 15 — GTM/GA4](./15-gtm-ga4.md): terpisah.