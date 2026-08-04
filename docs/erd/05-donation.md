# ERD — Modul 05: Donation

## Tabel: `donations`

Transaksi donasi ke campaign + atribusi lengkap.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `campaign_id` | uuid/ulid | FK → campaigns (**wajib**) | |
| `user_id` | uuid/ulid | FK → users (**nullable**) | Kosong jika guest |
| `donor_name` | string | | Selalu wajib |
| `donor_email` | string | | Selalu wajib |
| `donor_phone` | string | | Selalu wajib |
| `is_anonymous` | boolean | | Sembunyi nama publik, identitas tetap tersimpan |
| `amount` | decimal(15,2) | | Nominal |
| `message` | text | | Opsional |
| `payment_method` | enum | | VA, QRIS, e-wallet, kartu, dsb |
| `payment_status` | enum | | `pending` / `paid` / `failed` / `expired` / `refunded` |
| `payment_gateway_ref` | string | | ID transaksi gateway (rekonsiliasi) |
| `donation_type` | enum | | `one_time` / `recurring` |
| `utm_source` | string | | Atribusi |
| `utm_medium` | string | | |
| `utm_campaign` | string | | |
| `utm_content` | string | | |
| `utm_term` | string | | |
| `campaign_link_id` | uuid/ulid | FK → campaign_links (nullable) | Link asal |
| `page_visit_id` | uuid/ulid | FK → page_visits (nullable) | Kunjungan asal → funnel konversi |
| `paid_at` | timestamp | | Waktu konfirmasi sukses |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Catatan

### Guest vs Registered
- **Guest** → `user_id` null; identitas via `donor_email`/`donor_phone`. Tidak ada relasi ke `users`.
- **Registered** → `user_id` terisi → muncul di riwayat user.
- Jika guest kemudian daftar dengan email sama → klaim riwayat (match by email).

### Funnel Konversi
```
page_visits → donations (page_visit_id) → paid (paid_at)
```

### Status Midtrans → Internal
| Midtrans | Internal |
|---|---|
| `capture`/`settlement` | `paid` |
| `pending` | `pending` |
| `deny`/`cancel`/`expire` | `failed`/`expired` |

### Refund
- Mark `refunded` dengan catatan alasan.
- Eksekusi uang manual (gateway/bank), sistem hanya catat status.

## Relasi

```
donations.campaign_id ───────> campaigns.id (wajib)
donations.user_id ───────────> users.id      (nullable, guest)
donations.campaign_link_id ──> campaign_links.id (nullable)
donations.page_visit_id ─────> page_visits.id   (nullable, Modul 13)
```

## Keterkaitan
- [Modul 04 — Campaign](./04-campaign.md)
- [Modul 01 — Auth](./01-auth.md): identitas guest/registered.
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): funnel konversi.