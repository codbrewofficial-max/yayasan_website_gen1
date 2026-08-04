# Modul 05 — Donation

## Tujuan
Mengelola transaksi donasi ke campaign tertentu, termasuk integrasi payment gateway, notifikasi, bukti, restitution, dan atribusi.

## Struktur Data Inti (`donations`)

| Field | Keterangan |
|---|---|
| `campaign_id`, `tenant_id` | Wajib — donasi selalu terikat ke campaign spesifik |
| `user_id` | Nullable — kosong jika guest |
| `donor_name`, `donor_email`, `donor_phone` | Selalu wajib (walau guest), untuk kirim bukti/notifikasi |
| `is_anonymous` | Toggle "Hamba Allah" — sembunyi nama di tampilan publik, identitas asli tetap tersimpan internal (legal/audit) |
| `amount` | Nominal donasi |
| `message` | Opsional — doa/ucapan donatur |
| `payment_method` | VA, QRIS, e-wallet, kartu kredit, dsb |
| `payment_status` | `pending`, `paid`, `failed`, `expired`, `refunded` |
| `payment_gateway_ref` | ID transaksi dari payment gateway (untuk rekonsiliasi) |
| `donation_type` | `one_time` atau `recurring` |
| `utm_source`, `utm_medium`, `utm_campaign`, `utm_content`, `utm_term` | Atribusi UTM (dari Modul 4) untuk analisis channel |
| `campaign_link_id` | Link tracking asal donasi → join ke `link_clicks` (funnel konversi) |
| `page_visit_id` *(nullable)* | Tautan ke kunjungan halaman → **konversi kunjungan → donasi** |
| `paid_at` | Timestamp konfirmasi pembayaran sukses |

## Payment Gateway — Midtrans

- **Provider**: Midtrans.
- **Snap API** (rekomendasi) — donatur diarahkan ke halaman pembayaran Midtrans (cover VA semua bank, QRIS, e-wallet, kartu kredit) tanpa perlu bangun UI pembayaran sendiri.
- **Notification URL (webhook)** — endpoint khusus per environment (level backend pusat, bukan per tenant).
- **Signature verification** — wajib validasi `signature_key` tiap webhook sebelum diproses (mencegah fake notification "sudah bayar").
- **Status mapping** Midtrans → internal:

| Midtrans | Internal |
|---|---|
| `capture` / `settlement` | `paid` |
| `pending` | `pending` |
| `deny` / `cancel` / `expire` | `failed` / `expired` |

### Flow Pembayaran
1. Donatur pilih campaign + isi nominal → sistem create transaction ke gateway → dapat payment instruction (VA number/QRIS code/redirect link).
2. **Webhook handler** (paling krusial, wajib idempotent — jika webhook dikirim dobel, tidak boleh double-update/double-notifikasi) saat status berubah.
3. Setelah `paid` terkonfirmasi → update `collected_amount` campaign, kirim struk/bukti otomatis, catat atribusi UTM.

## Recurring Donation — Resmi Fase 2

- Struktur `donation_type` enum tetap disiapkan sekarang (hindari migrasi besar nanti).
- **Logic eksekusi** (scheduler, tokenisasi metode bayar, halaman "Kelola Donasi Rutin Saya", retry logic saat charge gagal) masuk **backlog fase 2**.
- MVP fokus **one_time** donation dengan Midtrans Snap.

## Notifikasi & Bukti Donasi

- Setelah `paid`, auto kirim **e-receipt** (email + opsional WA) — bukan sekadar notifikasi, tapi bukti resmi (misal untuk pajak/CSR perusahaan).
- Format PDF sederhana: nominal, tanggal, campaign, nomor referensi transaksi.

## Refund & Rekonsiliasi

- Admin Yayasan / Super Admin bisa **mark** donasi jadi `refunded` dengan catatan alasan (salah transfer, duplikasi, campaign dibatalkan).
- **Refund default** (proses uang balik) dilakukan manual lewat gateway/bank — sistem hanya mencatat status, bukan otomatis eksekusi kecuali gateway mendukung refund API.

## Keterkaitan
- [Modul 04 — Campaign](./04-campaign.md): target campaign (collected_amount di-update dari sini).
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): sumber data donasi untuk insight & funnel konversi.
- [Modul 01 — Auth](./01-auth.md): identitas donatur (guest / registered).