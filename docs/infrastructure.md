# Infrastruktur & Teknologi Stack

Dokumen keputusan teknologi & infrastruktur untuk platform donasi yayasan (Yayasan Website Gen1).

## Ringkasan Keputusan

| Aspek | Keputusan |
|---|---|
| Arsitektur aplikasi | Monolith Laravel (backend + admin + public site) |
| Versi | Laravel 11/12 · PHP 8.3 |
| Frontend | Blade + Livewire (server-rendered) |
| Database | MySQL (bawaan hosting) |
| Hosting | cPanel Shared Hosting |
| Pengembangan | Windows + Laragon |
| Queue & scheduler | Database queue + cron hosting |
| Build asset | Precompile lokal di Windows |
| Payment | Midtrans (webhook) |
| RBAC | spatie/laravel-permission |
| Auth | Sanctum (token) + 2FA TOTP |

---

## 1. Arsitektur Aplikasi

**Monolith satu codebase, satu deploy**:

```
1 codebase Laravel
├── Backend (API/logic, multi-tenant)
├── Admin Panel (Blade/Livewire, role-based)
└── Public Website (templated renderer, per tenant)
```

- Satu build → deploy sekali (paling sederhana untuk SDM kecil).
- Frontend server-rendered (Blade/Livewire) → SEO bagus, tanpa SPA, tanpa Node di server.

## 2. Versi & Stack Detail

| Komponen | Versi/Driver |
|---|---|
| PHP | 8.3 |
| Laravel | 11/12 |
| Frontend | Blade + Livewire |
| Database | MySQL |
| Auth API | Laravel Sanctum (Bearer token) |
| RBAC | spatie/laravel-permission |
| Multi-tenancy | Row-level (`tenant_id` global scope) — bukan DB-per-tenant |
| Payment | Midtrans (Snap API + webhook) |
| Asset | Vite (precompile lokal) |
| Queue | Driver `database` |
| Scheduler | `php artisan schedule:run` via cron |

## 3. Hosting: cPanel Shared

### Kenapa Shared Hosting
- Budget kecil; VPS murah sering error di jam tertentu.
- Cukup untuk skala awal; tidak perlu Docker/Nginx management.

### Batasan & Solusi

| Batasan cPanel Shared | Solusi |
|---|---|
| Tanpa Docker / reverse proxy custom | Pakai Apache/LiteSpeed bawaan + AutoSSL |
| Tanpa Supervisor (long-running worker) | Queue driver `database` + cron tiap menit |
| Composer tidak tersedia di server | `composer install` lokal → upload `vendor/` |
| Tanpa Node untuk build | Build asset lokal (Vite) → upload hasil build |
| Wildcard SSL tergantung host | **Perlu dicek**; fallback AutoSSL per subdomain |

### Deploy
- Docroot arah ke folder `public/` aplikasi.
- Env via file `.env` (disiapkan di server).
- Migrasi & seed dijalankan via cron/SSH (atau satu kali saat setup).

## 4. Queue & Scheduler

- **Queue**: driver `database` (tabel `jobs`), diproses lewat scheduler.
- **Scheduler**: `php artisan schedule:run` dipanggil cron hosting **setiap menit**.
- Tanpa long-running worker — job yang berat dihindari; webhook handler harus cepat.

### Job yang Berjalan via Queue (contoh)
- Kirim email/e-receipt donasi.
- Notifikasi WA/email (approve tenant, dll).
- Upload backup ke Google Drive.
- Cek DNS custom domain.

## 5. Build Asset

- Build dilakukan di Windows lokal: `npm run build` (Vite).
- Hasil build di-commit/di-upload bersama kode.
- Server tidak perlu Node.

## 6. Resolusi Tenant (di Shared Hosting)

```
Laravel middleware baca HOST header
  ├─ cocokkan custom_domain (tenants.custom_domain) → resolve tenant
  └─ else parse subdomain (tenants.subdomain) → resolve tenant
  └─ jika tidak ditemukan → 404 / "domain tidak ditemukan"
```

- Domain utama + subdomain (`*.namaplatform.com`) + custom domain.
- Custom domain per yayasan = **addon domain di cPanel** arah ke app yang sama (Host header yang menentukan tenant).

## 7. Domain Strategy

| Jenis | Pengelolaan | SSL |
|---|---|---|
| Domain utama `namaplatform.com` | Dipasang di cPanel | AutoSSL |
| Subdomain `yayasanA.namaplatform.com` | Wildcard subdomain (perlu dicek dukungan) | AutoSSL / wildcard cert |
| Custom domain `yayasanA.org` | **Addon domain cPanel** → app yang sama | AutoSSL per domain |

- Setup custom domain semi-manual per tenant (support burden diakui).

## 8. Storage & Backup

- **Storage media**: local disk hosting (`storage/`), struktur per tenant.
- **Backup**: ke **Google Drive** via Google Drive API (service account), folder per tenant; dijalankan scheduler + manual.
- Database backup via mysqldump (atau tool bawaan cPanel) → di-upload ke Google Drive.

## 9. Pengembangan Lokal (Windows)

- **Laragon** (PHP 8.3, MySQL, Apache/Nginx) — rekomendasi.
- Wildcard localhost untuk tes subdomain.
- Same PHP version di lokal & server (8.3) untuk konsistensi.

## 10. Keamanan

- Sanctum token + 2FA TOTP (wajib Super Admin, opsional Admin Yayasan).
- Signature verification pada webhook Midtrans.
- `.env` tidak pernah di-commit.
- Rate limiting pada endpoint auth.
- Soft delete + audit log (`audit_logs`) mencatat semua perubahan.

---

## Keterkaitan Dokumen Lain
- [Dokumentasi Modul](../modules/index.md)
- [ERD Master](../erd/index.md)