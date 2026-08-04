# ERD — Modul 01: Auth

## Tabel: `users`

Data dasar semua user (Super Admin, Admin Yayasan, Staff, Donatur).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Nullable; wajib untuk user internal, null untuk Super Admin |
| `name` | string | | Nama lengkap |
| `email` | string | UQ | Email login |
| `phone` | string | UQ (nullable) | Nomor WA (untuk OTP/notifikasi) |
| `password` | string | | Hash (nullable untuk donatur OTP-only) |
| `email_verified_at` | timestamp | | Verifikasi email |
| `two_factor_secret` | text | | Secret TOTP (2FA) |
| `two_factor_recovery_codes` | text | | Kode recovery |
| `is_active` | boolean | | Aktif/nonaktif |
| `avatar_id` | uuid/ulid | FK → media (nullable) | Foto profil |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

### Catatan
- **2FA**: wajib untuk Super Admin, opsional untuk Admin Yayasan.
- **Donatur**: bisa self-register atau guest (tanpa akun). Identitas guest disimpan langsung di `donations.donor_email`/`donor_phone`, tanpa relasi ke `users` (lihat Modul 05).
- Soft delete tetap menjadi referensi di audit log (pelaku tetap tampil).

## Tabel: `personal_access_tokens`

Token Sanctum Bearer.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | bigint | PK | Auto increment |
| `tokenable_type` | string | | Polymorphic (App\Models\User) |
| `tokenable_id` | uuid/ulid | FK | ID user |
| `name` | string | | Nama token |
| `token` | string | UQ | Hash token |
| `abilities` | text | | Abilities (nullable) |
| `last_used_at` | timestamp | | |
| `expires_at` | timestamp | | |
| `created_at`, `updated_at` | timestamp | | |

## Tabel: `password_reset_tokens`

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `email` | string | PK | |
| `token` | string | | Token reset |
| `created_at` | timestamp | | |

## Relasi

```
users.tenant_id ──────────> tenants.id         (untuk user internal)
users.avatar_id ──────────> media.id           (nullable)
personal_access_tokens ───> users (polymorphic)
users ── N:N ── roles ──── via model_has_roles (Modul 11, scoped tenant)
```

## Keterkaitan
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): role & permission user.
- [Modul 05 — Donation](./05-donation.md): guest vs registered donatur.