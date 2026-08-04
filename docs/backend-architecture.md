# Backend Architecture — Platform Donasi Yayasan (Yayasan Website Gen1)

Dokumen arsitektur backend berdasarkan keputusan diskusi:
- Admin panel: tenant dari **user login** (scope), bukan Host
- Service layer: Controller tipis + Services
- Super Admin dapat **bypass** global scope (lintas tenant)
- Monolith Laravel, single DB, row-level multi-tenancy

---

## Ringkasan Keputusan

| Aspek | Keputusan |
|---|---|
| Arsitektur | Monolith Laravel (backend + admin + public) |
| Multi-tenancy | Row-level (`tenant_id` global scope) — bukan DB-per-tenant |
| Tenant di admin panel | Dari **user login** (scope), bukan Host |
| Resolve tenant di public | Dari **Host header** (custom_domain / subdomain) |
| Logic bisnis | Controller tipis + **Service layer** |
| RBAC | spatie/permission (4 role tetap, scoped per tenant) |
| Bypass scope | Khusus **Super Admin** (helper `withoutTenantScope()`) |
| Queue | Driver `database` + cron (tanpa worker) |
| Audit | Global observer → `audit_logs` |

---

## 1. Alur Request

```
HTTP Request
   │
   ├─ 1. Resolve Tenant  → tenant_id (public: Host / admin: user login)
   │
   ├─ 2. Auth (Sanctum)  → user
   │
   ├─ 3. RBAC            → role & permission (spatie, scoped tenant)
   │
   ├─ 4. Global Scope    → where tenant_id otomatis (kecuali bypass Super Admin)
   │
   ├─ 5. Controller → Service → logic bisnis
   │
   ├─ 6. Observer        → tulis audit_logs
   │
   └─ 7. Queue (database) → job async
```

## 2. Resolve Tenant

### Public Website (dari Host)
```
Host header
  ├─ cocok custom_domain & active → tenant
  ├─ else parse subdomain → tenant
  └─ tidak ditemukan → 404
```

### Admin Panel (dari user login)
- Tenant konteks ditentukan dari **pivot RBAC user** (`model_has_roles.tenant_id`).
- Admin Yayasan / Staff → terkunci ke tenant pivot-nya.
- Super Admin → akses semua tenant (via switcher tenant).
- Tidak resolve dari Host.

### Cache
- Resolve tenant di-cache per-request (hindari query DB berulang).
- Cache resolusi Host → tenant (key: host) dengan invalidasi saat `custom_domain`/`subdomain` berubah.

## 3. Tenant Global Scope

- Semua model ber-tenant memakai `TenantScope` (Laravel Global Scope): otomatis `where tenant_id = current`.
- Model **tanpa** `tenant_id` (tidak ter-scope): `roles`, `permissions`, `templates`, `audit_logs`.
- **Bypass**: helper `withoutTenantScope()` — hanya diakses role **Super Admin** (dashboard platform). Enforce di Service/Policy, bukan sembarang.
- Saat user berpindah tenant (switcher) → scope berganti sesuai tenant konteks aktif.

## 4. RBAC Enforcement

- Middleware `role` + `permission` (spatie) untuk cek akses route.
- **Policy per model** untuk cek kepemilikan (misal: Admin Yayasan A tidak bisa edit campaign tenant B).
- Permission dihitung **per tenant konteks** (pivot `model_has_permissions` berisi `tenant_id`).
- 4 role tetap: `super_admin`, `admin_yayasan`, `staff_yayasan`, `donatur` — tanpa role kustom.

## 5. Struktur Kode

```
app/
├── Models/                  # model + relasi + global scope
│   ├── Tenant.php
│   ├── Campaign.php
│   ├── Donation.php
│   └── ...
├── Http/
│   ├── Controllers/         # thin controllers (web + api)
│   ├── Middleware/          # ResolveTenant, SetTenantScope, dll
│   └── Requests/            # FormRequest validasi
├── Services/                # logic bisnis reusable
│   ├── DonationService.php
│   ├── MediaService.php
│   ├── BackupService.php
│   └── ...
├── Observers/               # audit log otomatis
├── Jobs/                    # queue
│   ├── SendReceiptJob.php
│   ├── UploadBackupJob.php
│   └── CheckDomainDnsJob.php
├── Policies/                # RBAC per model
├── Actions/                 # (opsional) satu kelas per use-case
└── Providers/               # registrasi scope, observer, dll
```

### Service Layer
- Controller tipis → Service untuk logic bisnis.
- Reusable antar controller, mudah di-test.
- Contoh: `DonationService` (buat donasi, handle webhook, update collected_amount), `MediaService` (pipeline webp), `BackupService` (DB + assets + Google Drive).

## 6. Audit Log (Observer)

- Global observer/listener di semua model → tulis `audit_logs`.
- Field dicatat: `user_id`, `tenant_id`, `model_type`, `model_id`, `action`, `old_values`, `new_values`, `ip_address`, `user_agent`.
- **Pengecualian field sensitif** (misal password) dikonfigurasi agar tidak tercatat nilai aslinya.
- Akses lihat: Super Admin (semua) + Admin Yayasan (tenant-nya saja).

## 7. Queue & Jobs

- Driver `database`, diproses scheduler via cron tiap menit (`php artisan schedule:run`).
- Tanpa long-running worker — job harus ringan; webhook handler cepat.
- Contoh job: kirim e-receipt, notifikasi WA/email, upload backup ke Google Drive, cek DNS custom domain.

## 8. API & Routes

- **Web routes** (Blade/Livewire): admin panel + public site.
- **API routes** (Sanctum token): Midtrans webhook, form leads public, create donation.
- Midtrans webhook: endpoint terpusat (bukan per tenant), signature verification wajib, handler cepat & idempotent.

## 9. Keamanan

- Sanctum token + 2FA TOTP.
- Signature verification webhook Midtrans.
- Rate limiting endpoint auth.
- Policy ownership (cegah akses lintas tenant).
- Soft delete + audit log.

---

## Keterkaitan Dokumen Lain
- [Infrastruktur & Stack](./infrastructure.md)
- [ERD Master](../erd/index.md)
- [Roadmap MVP](./roadmap.md)