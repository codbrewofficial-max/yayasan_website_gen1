# ERD — Modul 14: Leads WhatsApp & Kontak

## Tabel: `leads`

Kontak masuk dari form kontak publik (via email atau WhatsApp).

| Kolom | Tipe | Kunci | Keterangan |
|---|---|---|---|
| `id` | uuid/ulid | PK | — |
| `tenant_id` | uuid/ulid | FK → tenants | Scoping |
| `name` | string | | Nama pengunjung |
| `email` | string | | Email pengunjung |
| `phone` | string | | Telepon/WA |
| `subject` | string | | Subjek |
| `topic` | string | | Kategori (Donasi, Kerja Sama, dsb) |
| `message` | text | | Isi pesan |
| `lead_type` | enum | | `email` / `whatsapp` (jalur dipilih) |
| `status` | enum | | `new` / `processing` / `closed` |
| `created_at` | timestamp | | Waktu input |
| `updated_at` | timestamp | | Untuk follow-up |
| `deleted_at` | timestamp | | Soft delete |

## Alur

```
Form kontak → backend: simpan lead
   ├─ lead_type=email   → kirim email ke yayasan (berisi input) → status new
   └─ lead_type=whatsapp → redirect wa.me (pesan carry input)  → status new
Dashboard: update new → processing → closed
```

## Relasi

```
leads.tenant_id ────> tenants.id
```

## Keterkaitan
- [Modul 12 — Website Configuration](./12-website-configuration.md): kontak/email config.
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): akses Super Admin & Admin Yayasan.