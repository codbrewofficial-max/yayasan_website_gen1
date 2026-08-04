# Modul 13 — Tracking & Insight

## Tujuan
**Analitik internal** milik platform sendiri — memproses data dari modul lain menjadi insight, **tanpa bergantung Google**. Berbeda dengan [Modul 15 — GTM/GA4](./15-gtm-ga4.md) yang punya Google (untuk SEO).

## Keputusan Kunci
- **Dua level insight**: Super Admin (agregasi seluruh platform + per yayasan) dan Admin Yayasan (per yayasan/campaign-nya).
- **Catat kunjungan granular** — tabel baru `page_visits` (halaman, sumber, perangkat, waktu).

## Sumber Data

### Dari modul lain (sudah ada)

| Sumber | Data | Modul Asal |
|---|---|---|
| Donations | Nominal, status, metode, waktu | [05 — Donation](./05-donation.md) |
| Campaign links | Klik, atribusi UTM | [04 — Campaign](./04-campaign.md) |
| Views count | Article / Program / Album (cache) | [06](./06-article.md), [03](./03-program.md), [07](./07-album-gallery.md) |

### Baru (Modul 13)

| Field | Keterangan |
|---|---|
| `tenant_id` | Scoping |
| `page_url` | Halaman yang dikunjungi |
| `source` | Asal pengunjung |
| `device_type` | mobile / desktop / tablet |
| `visited_at` | Waktu kunjungan |

Gabung dengan atribusi UTM untuk konversi pengunjung → donasi.

## Tiga Lapisan Insight

### 1. Visi lintas-campaign/tenant (Super Admin)
- Total donasi seluruh platform, tren per bulan/tahun.
- Channel paling efektif (dari atribusi UTM gabungan semua campaign).
- Performa tiap yayasan / tenant.
- Rata-rata konversi klik → donasi di platform.

### 2. Insight per campaign (Admin Yayasan)
- Progress dana terkumpul vs target (real-time).
- Link tracking mana paling menghasilkan donasi (diperbesar dari Modul 4).
- Distribusi donatur (nominal, metode pembayaran, one-time vs recurring).
- Tren donasi per waktu (grafik).

### 3. Insight per yayasan (Admin Yayasan)
- Review keseluruhan performa campaign + program.
- Perbandingan antar campaign dalam 1 yayasan.
- Konversi UTM per channel kampanye website-nya.

## Funnel Konversi (3 Tahap)
```
Kunjungan halaman (page_visits)
   → Donasi dibuat (donations, page_visit_id)
   → Pembayaran sukses (payment_status = paid, paid_at)
```

## Metrik yang Dicatat
- Views (artikel/program/album).
- Klik link tracking + atribusi donasi.
- Donasi: jumlah, nilai, metode, status.
- Konversi: klik → donasi → pembayaran sukses.
- Waktu & tren.

## Keterkaitan
- [Modul 04 — Campaign](./04-campaign.md): link tracking & atribusi.
- [Modul 05 — Donation](./05-donation.md): data donasi & `page_visit_id`.
- [Modul 15 — GTM/GA4](./15-gtm-ga4.md): analitik Google untuk SEO (paralel, tidak saling menggantikan).