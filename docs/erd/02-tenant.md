# ERD — Modul 02: Tenant

## Tabel: `tenants`

Master data profil yayasan + legalitas + domain.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `name` | string | | Nama yayasan |
| `subdomain` | string | **UQ** | Auto-generate saat onboarding |
| `custom_domain` | string | UQ (nullable) | Domain punya yayasan (jika upgrade) |
| `logo_id` | uuid/ulid | FK → media (nullable) | Logo |
| `category` | string | | Segmen (pendidikan, pesantren, sosial, dsb) |
| `status` | enum | | `draft` / `pending_verification` / `active` / `rejected` / `suspended` |
| `storage_quota` | bigint | | Quota storage (bytes) |
| `verification_note` | text | | Catatan approve/reject Super Admin |
| `contact_email` | string | | |
| `contact_phone` | string | | |
| `address` | text | | |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | Soft delete |

## Tabel: `tenant_documents`

Dokumen legalitas yang diupload saat verifikasi.

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | |
| `type` | enum | | `akta`, `sk_kemenkumham`, `npwp`, `izin_pub` |
| `media_id` | uuid/ulid | FK → media | File dokumen |
| `status` | enum | | `valid` / `invalid` |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |
| `deleted_at` | timestamp | | |

### Status Lifecycle
```
draft → pending_verification → active / rejected → (suspended) → active lagi
```

### Catatan Verifikasi
- Dashboard antrian `pending_verification` untuk Super Admin.
- `verification_note` wajib saat approve/reject (audit trail).
- Re-submission flow tanpa daftar ulang.
- Notifikasi otomatis (email/WA) saat status berubah.

## Relasi

```
tenants 1──N  tenant_documents        (dokumen legalitas)
tenants 1──1  website_configs         (Modul 12)
tenants 1──1  technical_configs       (Modul 12)
tenants 1──1  gtm_configs             (Modul 15)
tenants 1──N  users                   (user internal, scoped)
tenants 1──N  [semua tabel konten & transaksi ber-tenant]
```

## Keterkaitan
- [Modul 12 — Website Configuration](./12-website-configuration.md)
- [Modul 01 — Auth](./01-auth.md): user internal ter-scope ke tenant.
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): pivot role di-scope tenant.