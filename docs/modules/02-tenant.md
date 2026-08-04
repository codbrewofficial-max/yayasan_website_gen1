# Modul 02 — Tenant

## Tujuan
Master data profil yayasan sebagai *tenant* di platform, termasuk verifikasi legalitas sebelum yayasan aktif.

## Status Lifecycle

```
draft → pending_verification → active / rejected → (bisa) suspended → active lagi
```

| Status | Keterangan |
|---|---|
| `draft` | Data baru diisi (oleh yayasan sendiri via form pendaftaran, atau diinput Super Admin) |
| `pending_verification` | Dokumen legal sudah lengkap, menunggu review Super Admin |
| `active` | Lolos verifikasi; subdomain aktif; Admin Yayasan bisa login & kelola konten/campaign |
| `rejected` | Dokumen tidak valid/lengkap; perlu catatan alasan penolakan agar yayasan tahu apa yang diperbaiki |
| `suspended` | Kill-switch sewaktu-waktu (penyalahgunaan, dokumen expired, dsb); bisa diaktifkan lagi setelah selesai |

## Verifikasi Manual (Wajib)

- **Dashboard antrian verifikasi** di Admin Panel Super Admin — list tenant `pending_verification`, dengan preview dokumen (Akta, SK Kemenkumham, izin PUB) tanpa perlu download manual.
- **Catatan/alasan wajib** diisi Super Admin saat approve/reject (audit trail + feedback jelas untuk yayasan).
- **Notifikasi otomatis** ke Admin Yayasan (email/WA) setiap status berubah: approved, rejected, suspended.
- **Re-submission flow** — jika ditolak, yayasan bisa perbaiki dokumen dan submit ulang tanpa daftar dari nol.

## Data & Kapabilitas

- Master data profil yayasan: nama, kontak, branding dasar.
- Legalitas: SK Kemenkumham, NPWP, izin PUB (dengan upload dokumen).
- Storage quota per tenant.
- Kill-switch suspend/reaktivasi oleh Super Admin.

## Domain Strategy (Resolusi Domain)

| Pendekatan | Status |
|---|---|
| Subdomain (`yayasanA.namaplatform.com`) | Didukung dari awal (wildcard SSL) |
| Custom domain (`yayasanA.org`) | Didukung dari awal (verifikasi DNS + issue SSL otomatis) |

### Skema Resolusi Tenant
- Tabel `tenants` memiliki kolom `subdomain` (unique, auto-generate saat onboarding) dan `custom_domain` (unique, nullable).
- Middleware resolusi tenant berjalan di setiap request public website:
  1. Ambil `Host` header.
  2. Cek ke kolom `custom_domain` — jika match & status `active` → resolve ke tenant itu.
  3. Jika tidak match, parse subdomain dari host → cocokkan ke kolom `subdomain`.
  4. Jika tidak ditemukan keduanya → 404 / halaman "domain tidak ditemukan".

### Alur Onboarding Custom Domain
```
Yayasan input domain → sistem tampilkan instruksi DNS (CNAME/A record)
→ job cek DNS berkala (background queue)
→ jika valid: trigger NPM API untuk proxy host + SSL
→ status "active" → custom domain mulai berfungsi
```

### Mitigasi Support Burden (SDM kecil)
- Indikator status jelas di admin: `pending / verifying / active / error` + pesan error spesifik.
- Auto-check berkala + notifikasi WA/email jika domain custom gagal resolve (proaktif).

## Keterkaitan
- [Modul 12 — Website Configuration](./12-website-configuration.md): konfigurasi branding & teknis per tenant.
- [Modul 01 — Auth](./01-auth.md): akses per role ke tenant.
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): penugasan role di-scope per tenant.