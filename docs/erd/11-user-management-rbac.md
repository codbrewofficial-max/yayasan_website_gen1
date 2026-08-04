# ERD — Modul 11: User Management + RBAC

## Tabel: `roles`

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | bigint | PK | Auto increment |
| `name` | string | UQ | `super_admin` / `admin_yayasan` / `staff_yayasan` / `donatur` |
| `guard_name` | string | | web/api |
| `created_at`, `updated_at` | timestamp | | |

## Tabel: `permissions`

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | bigint | PK | Auto increment |
| `name` | string | UQ | `article.create`, `donation.view`, `media.upload`, dsb |
| `guard_name` | string | | |
| `created_at`, `updated_at` | timestamp | | |

## Tabel: `role_has_permissions` (pivot)

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `role_id` | bigint | FK+PK | → roles |
| `permission_id` | bigint | FK+PK | → permissions |

## Tabel: `model_has_roles` (pivot, scoped tenant)

Berisi `tenant_id` untuk penugasan per tenant.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `role_id` | bigint | FK+PK | → roles |
| `model_type` | string | PK | App\Models\User |
| `model_id` | uuid | PK | → users |
| `tenant_id` | uuid/ulid | FK → tenants | **Scoping role per tenant** |

## Tabel: `model_has_permissions` (pivot, scoped tenant)

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `permission_id` | bigint | FK+PK | → permissions |
| `model_type` | string | PK | |
| `model_id` | uuid | PK | → users |
| `tenant_id` | uuid/ulid | FK → tenants | **Scoping permission per tenant** |

## Relasi

```
roles 1──N role_has_permissions N──1 permissions
users  1──N model_has_roles  N──1 roles       (scoped tenant)
users  1──N model_has_permissions N──1 permissions (scoped tenant)
tenants 1──N model_has_roles / model_has_permissions
```

## Catatan
- **4 role tetap** — tanpa role kustom.
- Admin Yayasan grant **direct permission** per user via `model_has_permissions` (khusus tenant-nya).
- Scoping `tenant_id` memungkinkan satu user jadi Admin di tenant A & Staff di tenant B.
- Tabel roles/permissions/platform **tanpa `tenant_id`** (master global).

## Keterkaitan
- [Modul 01 — Auth](./01-auth.md)
- [Modul 02 — Tenant](./02-tenant.md): scope per tenant.