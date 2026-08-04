# ERD — Modul 09: Pengurus/Anggota Yayasan

## Tabel: `members`

Profil publik pengurus/anggota yayasan (struktur organisasi), terpisah dari akun sistem.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `name` | string | | Nama lengkap |
| `group` | enum | | `pembina` / `pengawas` / `pengurus_inti` / `anggota` |
| `position` | string | | Jabatan (Ketua, Sekretaris, Bendahara, dsb) |
| `photo_id` | uuid/ulid | FK → media (nullable) | Foto profil |
| `bio` | text | | Deskripsi singkat (opsional) |
| `sort_order` | unsignedInteger | | Urutan dalam grup |
| `status` | enum | | `active` / `inactive` |
| `joined_at` | year | | Tahun mulai menjabat (opsional) |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Relasi

```
members.tenant_id ────> tenants.id
members.photo_id ──────> media.id (nullable)
```

## Catatan
- **Terpisah dari `users`** — tidak ada FK ke akun login (profil publik murni).
- `sort_order` integer increment per group/tenant.

## Keterkaitan
- [Modul 10 — Media Library](./10-media-library.md): foto profil.
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): **tidak terhubung**.