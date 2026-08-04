# ERD — Modul 17: Audit Log / Activity Log

## Tabel: `audit_logs`

Mencatat semua perubahan di sistem (lintas-modul). Tabel platform — mencatat aksi tenant juga (via `tenant_id` nullable).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | bigint | PK | Auto increment (urutan log) |
| `tenant_id` | uuid/ulid | FK → tenants (nullable) | Null = aksi platform |
| `user_id` | uuid/ulid | FK → users (nullable) | Pelaku (null = sistem/guest) |
| `model_type` | string | | Nama model (programs, donations, dsb) |
| `model_id` | uuid/ulid | | ID record diubah |
| `action` | enum | | `create` / `update` / `delete` / `restore` / `login` |
| `old_values` | json | | Data sebelum (nullable) |
| `new_values` | json | | Data setelah (nullable) |
| `ip_address` | string | | IP pelaku |
| `user_agent` | string | | Browser/perangkat |
| `created_at` | timestamp | | Waktu aksi |

## Relasi

```
audit_logs.tenant_id ────> tenants.id (nullable)
audit_logs.user_id ──────> users.id   (nullable)
```

## Aturan Akses

| Role | Cakupan |
|---|---|
| Super Admin | Seluruh sistem + semua tenant |
| Admin Yayasan | Hanya tenant-nya sendiri |

## Catatan
- Disimpan **permanen** (tanpa rotasi/arsip).
- Soft-deleted user tetap tampil sebagai pelaku (referensi tidak terputus).
- Logging otomatis pada semua model aplikasi (observer/listener), bukan manual per modul.

## Keterkaitan
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md)
- [Modul 10 — Media Library](./10-media-library.md): perubahan asset terekam.