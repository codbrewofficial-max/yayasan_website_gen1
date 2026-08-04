# Modul 15 — GTM/GA4

## Tujuan
Menghubungkan public site tiap yayasan ke **Google Tag Manager (GTM)** dan **Google Analytics (GA4)** untuk mendukung SEO, dengan **ID berbeda per yayasan** (tiap yayasan punya properti Google sendiri — pola per-tenant).

## Keputusan Kunci
- **Custom events via GTM dataLayer** — event di-push ke `dataLayer`, tag GA4 di GTM yang meneruskan ke GA4. Tidak perlu snippet ekstra per event.
- **Custom events sesuai modul** — fixed list dari modul yang ada (tidak bisa ditambah sendiri via UI).

## Struktur Data Inti (`gtm_configs`)

| Field | Keterangan |
|---|---|
| `tenant_id` | Scoping |
| `gtm_id` | Kode GTM container (misal `GTM-XXXXXXX`) |
| `ga4_measurement_id` | Kode GA4 (misal `G-XXXXXXXXXX`) |
| `status` | `inactive` / `active` (toggle pasang/lepas snippet) |
| `updated_by` | Super Admin yang set |
| `updated_at` | Timestamp |

## Akses
- **Input/setup ID**: hanya **Super Admin** (inputan teknis, sesuai Modul 12).
- **Lihat data**: data analitik Google tetap di dashboard Google masing-masing yayasan, bukan di admin platform.

## Snippet Injection (per tenant)

GTM/GA4 snippet disuntikkan ke header/footer public site tiap yayasan sesuai `tenant_id` saat resolve domain. Karena tiap tenant berbeda, injection **dinamis per tenant** (bukan global).

```
Public site yayasan (resolve tenant)
   ├─ Inject GTM snippet (gtm_id tenant) di header
   └─ GA4 measurement id di-pass via config (dataLayer / tag GTM)
```

## Custom Events (fixed, push ke dataLayer)

| Event | Trigger |
|---|---|
| `donation_started` | Mulai isi form donasi |
| `donation_completed` | Donasi sukses (kirim nominal + campaign_id) |
| `campaign_link_click` | Klik short link ber-UTM |
| `lead_submitted` | Submit form kontak |
| `article_view` / `program_view` | Lihat konten |

Flow: **Event → dataLayer → tag GA4 (di GTM) → GA4**.

## Keterkaitan
- [Modul 12 — Website Configuration](./12-website-configuration.md): set-up teknis per tenant.
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): analitik internal (paralel, tidak saling menggantikan GTM/GA4).