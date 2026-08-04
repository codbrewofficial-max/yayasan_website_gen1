# Modul 11 — User Management + RBAC

## Tujuan
Mengelola pengguna internal dan hak aksesnya (RBAC). Berbeda dari Modul 1 (Auth) yang menangani *mekanisme* masuk (login, token, OTP, reset password).

| | Modul 1: Auth | Modul 11: User Management + RBAC |
|---|---|---|
| Fokus | Login, token (Sanctum), OTP/2FA, reset password | CRUD user, undangan, aktivasi, arsip + **role/permission** |

## Keputusan Kunci
- **Penugasan scoped per tenant** — satu user bisa punya role berbeda di tenant berbeda (Admin di tenant A, Staff di tenant B). Pivot berisi `tenant_id`.
- **4 role tetap saja** — tanpa role kustom. Admin Yayasan tidak bisa membuat role baru; hanya bisa grant **direct permission** tambahan per user.
- **Fokus pengguna internal** — akun donatur tidak dikelola manual (otomatis via registrasi/guest, bisa lihat riwayat sendiri).

## 4 Role & Tanggung Jawab

| Role | Level | Tanggung Jawab |
|---|---|---|
| **Super Admin** | Platform | Kelola semua tenant, verifikasi yayasan, kill-switch, monitoring transaksi, konfigurasi platform |
| **Admin Yayasan** | Tenant | Akses penuh data yayasan sendiri + kelola Staff (undang, nonaktifkan, grant permission) |
| **Staff Yayasan** | Tenant | Akses terbatas sesuai permission yang di-grant Admin Yayasan |
| **Donatur** | Publik | Lihat riwayat donasi sendiri, kelola data pribadi (di luar scope modul ini) |

## Konsep RBAC (pola spatie/permission)

Empat entitas inti:

```
users                          roles                permissions
├─ Super Admin (platform)      ├─ super_admin        ├─ article.create
├─ Admin Yayasan (tenant)      ├─ admin_yayasan      ├─ article.publish
├─ Staff Yayasan (tenant)      ├─ staff_yayasan      ├─ donation.view
└─ Donatur (publik)            └─ donatur            ├─ media.upload
                                      │             └─ ...
                             role_has_permissions   (role punya permission apa)
                             model_has_roles        (user punya role apa, scoped tenant_id)
                             model_has_permissions  (user dapat permission ekstra, scoped tenant_id)
```

## Data Model

| Entitas | Keterangan |
|---|---|
| `users` | Data dasar user + `tenant_id` (untuk internal) |
| `roles` | 4 fixed: `super_admin`, `admin_yayasan`, `staff_yayasan`, `donatur` |
| `permissions` | List permission per fitur (`article.create`, `article.publish`, `donation.view`, `media.upload`, dsb) |
| `model_has_roles` | Penugasan user → role, **termasuk `tenant_id`** (scoping) |
| `role_has_permissions` | Default permission per role |
| `model_has_permissions` | Direct permission tambahan per user, scoped `tenant_id` |

## Dua Tingkat Permission
1. **Role permission** — default permission yang melekat pada role (misal `staff_yayasan` bawaan punya `article.create`).
2. **Direct permission** — Admin Yayasan bisa kasih permission ekstra per user tanpa mengubah role-nya.

## Keterkaitan
- [Modul 01 — Auth](./01-auth.md): mekanisme masuk.
- [Modul 02 — Tenant](./02-tenant.md): penugasan role di-scope per tenant.
- [Modul 12 — Website Configuration](./12-website-configuration.md): hak akses ke konfigurasi (teknis vs operasional).