# Business Logic — Platform Donasi Yayasan (Yayasan Website Gen1)

Dokumen detail business logic untuk alur paling krusial: **Onboarding Tenant**, **Donation Flow**, dan **Midtrans Webhook**.

## Ringkasan Keputusan

| Alur | Keputusan |
|---|---|
| Identitas pembayaran | `order_id` ber-prefix tenant: `T{tenantShort}-{uuid}` → webhook bisa resolve tenant |
| Upload dokumen legal | Via **Media Library** (gambar auto webp, dokumen disimpan) |
| Payment gagal | **Retry otomatis** — user coba ulang dengan transaksi baru; expired di-tandai scheduler |

---

## 1. Donation Flow

### Alur Lengkap

```
1. Donatur di public site `/donasi/{campaign}`
   → pilih nominal, donor_name, donor_email, donor_phone, opsi anonim, pesan
2. Validasi (DonationService):
   - campaign status active?
   - target_amount belum tercapai?
   - end_date belum lewat?
3. Buat order:
   - record donations (status=pending)
   - atribusi: utm_*, campaign_link_id, page_visit_id, user_id (nullable)
   - order_id = "T{tenantShort}-{uuid}" (prefix tenant)
4. Panggil Midtrans Snap API → snap_token / redirect_url
5. Donatur membayar di halaman Midtrans
6. Webhook Midtrans update status → donations
7. Setelah paid:
   - update payment_status=paid, paid_at
   - update campaigns.collected_amount
   - kirim e-receipt (queue)
   - trigger event GA4 `donation_completed`
   - catat audit log
```

### Atribusi (disimpan saat create)
| Field | Sumber |
|---|---|
| `utm_source/medium/campaign/content/term` | Cookie/session (Modul 4) |
| `campaign_link_id` | Link tracking |
| `page_visit_id` | Tracking kunjungan (Modul 13) |
| `user_id` | Registered (nullable — guest kosong) |

### Retry Otomatis (keputusan)
- **Failed / expired** → donatur bisa mencoba ulang dengan **transaksi baru**; status lama tetap tercatat.
- **Scheduler** menandai `pending` yang melewati batas waktu sebagai `expired` otomatis.
- Tidak ada charge ulang otomatis (recurring = fase 2).

### Edge Cases
- **Duplikasi**: idempotent — anti double order saat refresh/submit ganda.
- **Nominal**: validasi minimal & format rupiah.
- **Anonim**: `is_anonymous` disembunyikan di publik, identitas tetap tersimpan.
- **Campaign berubah di tengah** (pause/end): tolak atau beri notice saat submit.

---

## 2. Midtrans Webhook

### Alur Handler (harus cepat & aman)

```
1. POST /api/midtrans/notification
2. Signature verification (wajib) — validasi signature_key dengan server key → tolak jika invalid
3. Parse order_id → ambil prefix tenant → resolve tenant + Midtrans config
4. Cari record donations (by payment_gateway_ref / order_id)
5. Idempotent: jika sudah paid/settlement → jangan proses ulang (anti double-notifikasi)
6. Mapping status:
   capture/settlement → paid
   pending            → pending
   deny/cancel/expire → failed / expired
7. Jika paid (baru):
   - update payment_status=paid, paid_at
   - update campaigns.collected_amount
   - kirim e-receipt (queue)
   - trigger event GA4 `donation_completed`
   - catat audit log
8. Return 200 secepatnya (proses berat lewat queue)
```

### Keputusan
- **`order_id` ber-prefix tenant** → webhook resolve tenant & memilih Midtrans config yang benar (karena tiap tenant punya akun Midtrans sendiri).
- **Signature verification** wajib + rate limit.
- **Idempotent** — proses ulang dilarang.
- **Response cepat** — e-receipt/notifikasi via queue (tanpa long-running worker).

### Mapping Status Midtrans → Internal
| Midtrans | Internal |
|---|---|
| `capture` / `settlement` | `paid` |
| `pending` | `pending` |
| `deny` / `cancel` / `expire` | `failed` / `expired` |

---

## 3. Onboarding Tenant

### Alur Lengkap

```
1. Pendaftaran (form yayasan / input Super Admin) → status draft
2. Input dokumen legal: Akta, SK Kemenkumham, NPWP, izin PUB
   → upload via Media Library → submit → pending_verification
3. Super Admin review (dashboard antrian):
   - preview dokumen langsung
   - approve → active (wajib catatan)
   - reject → rejected (wajib catatan; yayasan bisa perbaiki & resubmit)
4. Setup otomatis setelah active (job):
   - aktifkan subdomain + resolusi tenant
   - buat Admin Yayasan default (undangan)
   - buat default content sections website
   - assign storage quota
5. Custom domain (opsional):
   - instruksi DNS (CNAME/A record)
   - cek berkala (queue) → jika valid, aktifkan + SSL
```

### Dokumen Legal via Media Library
- Disimpan di `tenant_documents`: `type` (akta/sk_kemenkumham/npwp/izin_pub) + `media_id`.
- Gambar auto-proses webp; dokumen (PDF/Word/PPT/Excel) disimpan asli.
- Preview langsung di dashboard Super Admin (tanpa download manual).

### Status Lifecycle
```
draft → pending_verification → active / rejected → (suspended) → active lagi
```

### Notifikasi
- Auto email/WA ke Admin Yayasan setiap status berubah (approved/rejected/suspended).
- Re-submission flow setelah reject (tanpa daftar ulang).

---

## Keterkaitan Dokumen Lain
- [Modul 02 — Tenant](../modules/02-tenant.md)
- [Modul 05 — Donation](../modules/05-donation.md)
- [Modul 04 — Campaign](../modules/04-campaign.md)
- [Backend Architecture](./backend-architecture.md)
- [Infrastruktur & Stack](./infrastructure.md)