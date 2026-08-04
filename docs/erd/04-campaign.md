# ERD — Modul 04: Campaign

## Tabel: `campaigns`

Penggalangan dana spesifik di bawah Program.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `program_id` | uuid/ulid | FK → programs (**wajib**) | Induk |
| `title` | string | | Judul campaign |
| `slug` | string | UQ (per tenant) | SEO-friendly |
| `story` | longText | | Cerita/latar belakang |
| `target_amount` | decimal(15,2) | | Nullable (open-ended) |
| `collected_amount` | decimal(15,2) | | Computed/cached dari donations |
| `start_date` | date | | |
| `end_date` | date | | Nullable (open-ended) |
| `status` | enum | | `draft` / `active` / `paused` / `completed` / `expired` |
| `featured_image_id` | uuid/ulid | FK → media (nullable) | |
| `donation_type` | enum | | `one_time` / `recurring` (recurring fase 2) |
| `show_donor_list` | boolean | | Tampilkan daftar donatur publik |
| `allow_anonymous` | boolean | | Opsi donatur anonim |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Tabel: `campaign_links`

Short link ber-UTM untuk tracking.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `campaign_id` | uuid/ulid | FK → campaigns | |
| `label` | string | | Nama internal (Bio IG, Broadcast WA) |
| `utm_source` | string | | instagram, whatsapp, email, dsb |
| `utm_medium` | string | | social, cpc, email, referral |
| `utm_campaign` | string | | Default slug, bisa override |
| `utm_content` | string | | Opsional (A/B) |
| `utm_term` | string | | Opsional (A/B) |
| `short_code` | string | UQ | Kode short link global (misal `Xk9dP2`) |
| `target_url` | string | | URL lengkap + UTM |
| `clicks_count` | unsignedInteger | | **Cache** agregat dari link_clicks |
| `last_clicked_at` | timestamp | | Cache |
| `created_by` | uuid/ulid | FK → users | |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | |

## Tabel: `link_clicks`

Log per klik — pondasi tren per waktu.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `campaign_link_id` | uuid/ulid | FK → campaign_links | |
| `referrer` | string | | Asal pengunjung |
| `device_type` | enum | | mobile / desktop / tablet |
| `clicked_at` | timestamp | | Waktu klik |
| `created_at` | timestamp | | |

## Relasi

```
campaigns.program_id ──────────────> programs.id (wajib)
campaigns.featured_image_id ───────> media.id
campaigns 1──N  campaign_links 1──N link_clicks
campaigns 1──N  donations           (Modul 05)
campaign_links 1──N donations       (donations.campaign_link_id nullable)
```

## Keterkaitan
- [Modul 03 — Program](./03-program.md): induk.
- [Modul 05 — Donation](./05-donation.md): atribusi link → donasi.
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): agregasi klik & konversi.