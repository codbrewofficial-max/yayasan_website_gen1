# Modul 16 — Restore & Backup Data

## Tujuan
Membackup database dan asset **per yayasan**, dengan scope bergantung role, dan menyimpannya ke **Google Drive**.

## Keputusan Kunci
- **Restore bisa Admin Yayasan** — selain backup, Admin Yayasan juga boleh restore data tenant-nya sendiri (scope dibatasi ke tenant-nya).
- **Otomatis + manual** — backup terjadwal (harian/mingguan) + opsi backup manual kapan saja.

## Scope Backup

| Role | Scope | Isi Backup |
|---|---|---|
| **Admin Yayasan** | Tenant-nya sendiri saja | Data DB tenant + asset (media library) tenant |
| **Super Admin** | Seluruh sistem | Semua data semua tenant + konfigurasi platform |

## Jenis Data yang Dibackup
1. **Database** — data tenant (dari single DB, difilter `tenant_id`); untuk Super Admin = full DB.
2. **Assets** — file media library tenant (webp images, dokumen) dari storage.

## Alur

```
Backup:
   Manual (Admin Yayasan: tenant-nya / Super Admin: platform) → job
   Scheduled (auto, harian/mingguan)                          → job
   Job: backup DB (filter tenant_id / full) + assets → upload Google Drive
   → catat record backup (status, file_id, size)

Restore:
   Admin Yayasan → restore tenant-nya sendiri (dari file backup tenant-nya)
   Super Admin   → restore tenant mana pun / platform
```

## Mekanisme
- **Manual trigger**: Admin Yayasan klik "Backup Sekarang" → job berjalan → upload ke Google Drive (folder per tenant).
- **Scheduled**: opsi otomatis terjadwal (harian/mingguan), umumnya untuk Super Admin.
- **Storage Google Drive**: integrasi Google Drive API + service account, struktur folder per tenant.
- **Restore**: proses mengembalikan data dari file backup (DB + asset); butuh kehati-hatian.

## Struktur Data Inti (`backups`)

| Field | Keterangan |
|---|---|
| `tenant_id` | Scoping (null = backup platform) |
| `type` | `database` / `assets` / `full` |
| `triggered_by` | Admin Yayasan / Super Admin |
| `scope` | `tenant` / `platform` |
| `file_id` | ID file di Google Drive |
| `file_size` | Ukuran backup |
| `status` | `pending` / `in_progress` / `success` / `failed` |
| `created_at` | Timestamp |

## Keterkaitan
- [Modul 02 — Tenant](./02-tenant.md): scope data per tenant.
- [Modul 10 — Media Library](./10-media-library.md): assets yang dibackup.