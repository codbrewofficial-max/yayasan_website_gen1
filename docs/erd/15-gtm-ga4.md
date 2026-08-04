# ERD — Modul 15: GTM/GA4

## Tabel: `gtm_configs`

Konfigurasi Google Tag Manager & Google Analytics per tenant (setup Super Admin).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `tenant_id` | uuid/ulid | PK+FK → tenants | 1:1 |
| `gtm_id` | string | | `GTM-XXXXXXX` |
| `ga4_measurement_id` | string | | `G-XXXXXXXXXX` |
| `status` | enum | | `inactive` / `active` |
| `updated_by` | uuid/ulid | FK → users | Super Admin |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

## Arsitektur

```
Public site yayasan (resolve tenant)
   ├─ inject GTM snippet (gtm_id) di header
   └─ GA4 measurement id via config/dataLayer

Event → dataLayer → tag GA4 (GTM) → GA4
```

## Custom Events (fixed, push ke dataLayer)

| Event | Trigger |
|---|---|
| `donation_started` | Mulai isi form donasi |
| `donation_completed` | Donasi sukses (nilai + campaign_id) |
| `campaign_link_click` | Klik short link ber-UTM |
| `lead_submitted` | Submit form kontak |
| `article_view` / `program_view` | Lihat konten |

## Relasi

```
gtm_configs.tenant_id ────> tenants.id (PK, 1:1)
```

## Keterkaitan
- [Modul 12 — Website Configuration](./12-website-configuration.md): setup teknis.
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): paralel (internal vs Google).