# Dokumentasi Modul — Platform Donasi Yayasan (Yayasan Website Gen1)

Dokumentasi setiap modul dalam satu file per modul. Format & keputusan berdasarkan diskusi desain (konsep, belum finalisasi teknologi).

> [Kembali ke navigasi utama](../README.md)

## Arsitektur Inti

- **1 Backend** — Laravel (konsep), single database + `tenant_id` scoping (bukan DB-per-tenant)
- **1 Admin Panel** — role-based (Super Admin, Admin Yayasan, Staff)
- **N Public Website** — templated renderer, resolusi domain ganda (subdomain + custom domain dari awal)
- Model bisnis: **managed platform / setengah SaaS** — yayasan cukup pakai, infra dipegang Labkerkomit

## Daftar Modul

| No | Modul | File |
|---|---|---|
| 01 | Auth | [`01-auth.md`](./01-auth.md) |
| 02 | Tenant | [`02-tenant.md`](./02-tenant.md) |
| 03 | Program | [`03-program.md`](./03-program.md) |
| 04 | Campaign | [`04-campaign.md`](./04-campaign.md) |
| 05 | Donation | [`05-donation.md`](./05-donation.md) |
| 06 | Article | [`06-article.md`](./06-article.md) |
| 07 | Album Gallery | [`07-album-gallery.md`](./07-album-gallery.md) |
| 08 | Gallery | [`08-gallery.md`](./08-gallery.md) |
| 09 | Pengurus/Anggota Yayasan | [`09-pengurus.md`](./09-pengurus.md) |
| 10 | Media Library | [`10-media-library.md`](./10-media-library.md) |
| 11 | User Management + RBAC | [`11-user-management-rbac.md`](./11-user-management-rbac.md) |
| 12 | Website Configuration | [`12-website-configuration.md`](./12-website-configuration.md) |
| 13 | Tracking & Insight | [`13-tracking-insight.md`](./13-tracking-insight.md) |
| 14 | Leads WhatsApp & Kontak | [`14-leads-wa-kontak.md`](./14-leads-wa-kontak.md) |
| 15 | GTM/GA4 | [`15-gtm-ga4.md`](./15-gtm-ga4.md) |
| 16 | Restore & Backup Data | [`16-restore-backup.md`](./16-restore-backup.md) |
| 17 | Audit Log / Activity Log | [`17-audit-log.md`](./17-audit-log.md) |

## Hierarki Data Utama

```
Program (induk kegiatan)
   └── Campaign (penggalangan dana spesifik)
          └── Donation (transaksi donasi ke campaign)

Album Gallery (induk koleksi)
   └── Gallery (item foto + judul)

Media Library (bank aset) → dipakai semua modul (thumbnail/foto/dokumen)
```
