# ERD — Modul 13: Tracking & Insight

## Tabel: `page_visits`

Log kunjungan halaman secara granular (modul ini).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `page_url` | string | | Halaman yang dikunjungi |
| `source` | string | | Asal pengunjung |
| `device_type` | enum | | mobile / desktop / tablet |
| `visited_at` | timestamp | | Waktu kunjungan |
| `created_at` | timestamp | | |

## Sumber Data (dari modul lain — untuk insight)

| Modul | Tabel | Data |
|---|---|---|
| 05 | `donations` | Nominal, status, metode, waktu |
| 04 | `campaign_links` | Klik, atribusi UTM |
| 04 | `link_clicks` | Rincian klik per waktu |
| 03/06/07 | `programs`/`articles`/`albums` | `views_count` (cache) |
| 13 | `page_visits` | Kunjungan granular |

## Relasi

```
page_visits.tenant_id ────────> tenants.id
page_visits 1──N  donations    (donations.page_visit_id nullable)
```

## Insight Level

1. **Super Admin (platform)**: total donasi, tren channel, performa per yayasan, konversi klik→donasi.
2. **Admin Yayasan (per campaign)** : progress, distribusi donatur, tren, perbandingan campaign.
3. **Admin Yayasan (per yayasan)**: performa keseluruhan, konversi UTM per channel.

## Funnel Konversi

```
page_visits → donations (page_visit_id) → payment_status=paid
```

## Keterkaitan
- [Modul 04 — Campaign](./04-campaign.md)
- [Modul 05 — Donation](./05-donation.md)
- [Modul 15 — GTM/GA4](./15-gtm-ga4.md): berjalan paralel (Google untuk SEO).