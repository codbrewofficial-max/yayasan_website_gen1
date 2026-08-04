# Modul 17 — Audit Log / Activity Log

## Tujuan
Mencatat **semua perubahan** yang terjadi di sistem untuk kepentingan audit & keamanan. Setiap aktivitas (create, update, delete, restore, login, dsb) terekam lengkap beserta siapa yang melakukannya.

## Keputusan Kunci
- **Modul tersendiri** — berdiri sebagai lapisan lintas-modul (Modul 17).
- **Dua level akses**: Super Admin (semua sistem) + Admin Yayasan (tenant-nya saja).
- **Soft delete tetap jadi referensi** — user yang dihapus (soft delete) tetap tampil sebagai pelaku di audit log.
- **Disimpan permanen** — tidak ada kebijakan rotasi/arsip untuk tabel ini.

## Struktur Data Inti (`audit_logs`)

| Field | Keterangan |
|---|---|
| `tenant_id` *(nullable)* | Null untuk aksi level platform; terisi untuk aksi di dalam tenant |
| `user_id` | Pelaku aksi (nullable jika aksi sistem/guest) |
| `model_type` | Nama model/entitas yang diubah (programs, donations, dsb) |
| `model_id` | ID record yang diubah |
| `action` | Jenis aksi: `create`, `update`, `delete`, `restore`, `login`, etc. |
| `old_values` | Data sebelum perubahan (JSON) |
| `new_values` | Data setelah perubahan (JSON) |
| `ip_address` | IP pelaku |
| `user_agent` | Browser/perangkat pelaku |
| `created_at` | Waktu aksi |

## Aturan Akses

| Role | Cakupan |
|---|---|
| **Super Admin** | Seluruh sistem + semua tenant |
| **Admin Yayasan** | Hanya tenant-nya sendiri |

## Keterkaitan
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): user soft delete tetap jadi referensi pelaku.
- [Modul 10 — Media Library](./10-media-library.md): perubahan asset terekam di sini.
- Tabel ini lintas-modul — semua tabel aplikasi otomatis tercatat perubahannya.