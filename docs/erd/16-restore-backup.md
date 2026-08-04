# ERD — Modul 16: Restore & Backup Data

## Tabel: `backups`

Rekam setiap operasi backup & restore (Scope per role).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants (nullable) | Null = backup platform |
| `type` | enum | | `database` / `assets` / `full` |
| `scope` | enum | | `tenant` / `platform` |
| `triggered_by` | uuid/ulid | FK → users | Admin Yayasan / Super Admin |
| `trigger_type` | enum | | `manual` / `scheduled` |
| `file_id` | string | | ID file Google Drive |
| `file_size` | bigint | | Ukuran backup |
| `status` | enum | | `pending` / `in_progress` / `success` / `failed` |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Scope Backup

| Role | Scope | Isi |
|---|---|---|
| Admin Yayasan | Tenant-nya saja | DB (filter tenant_id) + assets tenant |
| Super Admin | Seluruh sistem | Full DB + semua assets + config |

## Alur

```
Backup: manual (klik) / scheduled (harian-mingguan)
  → job backup DB (filter tenant_id / full) + assets
  → upload Google Drive (folder per tenant)
  → catat record backups

Restore: Admin Yayasan (tenant-nya) / Super Admin (mana pun/platform)
```

## Relasi

```
backups.tenant_id ───> tenants.id (nullable)
backups.triggered_by ─> users.id
```

## Keterkaitan
- [Modul 02 — Tenant](./02-tenant.md): scope data.
- [Modul 10 — Media Library](./10-media-library.md): assets yang dibackup.