# ERD Master — Platform Donasi Yayasan (Yayasan Website Gen1)

Dokumen ERD terpusat: gambaran seluruh tabel, relasi, dan standar global. Detail per modul ada di file ERD masing-masing.

> [Kembali ke navigasi utama](../README.md)

## Standar Global

| Standar | Keterangan |
|---|---|
| Primary key | **UUID/ULID** (keputusan teknologi finalisasi nanti) |
| Soft deletes | **Semua tabel** punya kolom `deleted_at` |
| Timestamps | Semua tabel punya `created_at`, `updated_at` |
| `tenant_id` | Di **semua tabel data tenant**; tabel platform **tanpa** `tenant_id` |
| Kolom urutan | Tabel yang butuh sorting pakai `sort_order` / `sequence` **integer increment per tenant** (bukan UUID) |

## Konvensi Kolom

- **PK**: `id` (uuid/ulid) — semua tabel.
- **FK**: `{tabel}_id` (uuid/ulid).
- **Soft delete**: `deleted_at` (timestamp, nullable) — semua tabel.
- **Tenant scope**: `tenant_id` (uuid/ulid, nullable untuk aksi platform) pada tabel data tenant.
- **Urutan**: `sort_order` (unsignedInteger) pada tabel berurutan (galleries, members, dsb).
- **Audit**: semua perubahan dicatat di `audit_logs` ([Modul 17](./17-audit-log.md)).

## Daftar Tabel

### Platform (tanpa `tenant_id`)

| Tabel | Modul | Keterangan |
|---|---|---|
| `roles` | 11 | Role tetap: super_admin, admin_yayasan, staff_yayasan, donatur |
| `permissions` | 11 | Daftar permission per fitur |
| `role_has_permissions` | 11 | Pivot: role → permissions |
| `templates` | 12 | Daftar template public site |
| `audit_logs` | 17 | Log semua perubahan sistem |

### RBAC / Auth

| Tabel | Modul | Keterangan |
|---|---|---|
| `users` | 1, 11 | User sistem + donatur |
| `personal_access_tokens` | 1 | Token Sanctum |
| `model_has_roles` | 11 | Pivot: user → role (**dengan `tenant_id`**) |
| `model_has_permissions` | 11 | Pivot: user → permission langsung (**dengan `tenant_id`**) |

### Master Tenant

| Tabel | Modul | Keterangan |
|---|---|---|
| `tenants` | 2 | Profil yayasan + legalitas + domain |
| `tenant_documents` | 2 | Dokumen legalitas (Akta, SK, izin PUB) |

### Konten (tenant_id)

| Tabel | Modul | Keterangan |
|---|---|---|
| `programs` | 3 | Induk kegiatan |
| `campaigns` | 4 | Penggalangan dana spesifik |
| `campaign_links` | 4 | Short link ber-UTM |
| `link_clicks` | 4 | Log klik per link |
| `articles` | 6 | Konten blog/berita |
| `albums` | 7 | Induk koleksi foto |
| `galleries` | 8 | Item foto + judul |
| `members` | 9 | Pengurus/anggota yayasan |

### Transaksi & Donasi

| Tabel | Modul | Keterangan |
|---|---|---|
| `donations` | 5 | Transaksi donasi + atribusi |

### Media & Aset

| Tabel | Modul | Keterangan |
|---|---|---|
| `media` | 10 | Bank aset (gambar/dokumen) |

### Konfigurasi & Insight

| Tabel | Modul | Keterangan |
|---|---|---|
| `website_configs` | 12 | Konfigurasi operasional website |
| `technical_configs` | 12 | Konfigurasi teknis (hidden) |
| `gtm_configs` | 15 | ID GTM/GA4 |
| `page_visits` | 13 | Log kunjungan halaman |
| `leads` | 14 | Kontak masuk (email/WA) |
| `backups` | 16 | Rekam backup/restore |

## Peta Relasi Utama (Ringkas)

```
tenants 1──N  users
tenants 1──1  website_configs / technical_configs / gtm_configs

programs  1──N  campaigns  1──N  donations
campaigns 1──N  campaign_links 1──N link_clicks

albums  1──N  galleries

media ← (FK langsung, tanpa pivot) programs/campaigns/articles/albums/galleries/members/users

donations ──> campaigns, media? (tidak) , users (nullable)
donations ──> campaign_links (campaign_link_id, nullable)
donations ──> page_visits (page_visit_id, nullable)

model_has_roles ──> roles, users, tenants (scope)
```

## Peta Relasi Media (Simple — FK langsung)

```
programs.featured_image_id ─┐
campaigns.featured_image_id ├──> media.id
articles.featured_image_id ─┤
albums.featured_image_id ───┤
galleries.image_id ─────────┘
members.photo_id ────────────> media.id
users.avatar_id ─────────────> media.id
```

Tidak ada tabel pivot `media_usage` — pemakaian media dilacak via query balik (FK langsung di tabel pemakai).

## Navigasi

- [01 Auth](./01-auth.md) · [02 Tenant](./02-tenant.md) · [03 Program](./03-program.md) · [04 Campaign](./04-campaign.md) · [05 Donation](./05-donation.md) · [06 Article](./06-article.md) · [07 Album Gallery](./07-album-gallery.md) · [08 Gallery](./08-gallery.md) · [09 Pengurus](./09-pengurus.md) · [10 Media Library](./10-media-library.md) · [11 User Management + RBAC](./11-user-management-rbac.md) · [12 Website Configuration](./12-website-configuration.md) · [13 Tracking & Insight](./13-tracking-insight.md) · [14 Leads](./14-leads.md) · [15 GTM/GA4](./15-gtm-ga4.md) · [16 Restore & Backup](./16-restore-backup.md) · [17 Audit Log](./17-audit-log.md)