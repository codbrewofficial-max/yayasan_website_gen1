# Modul 12 — Website Configuration

## Tujuan
Konfigurasi website per yayasan dengan **dua tingkat akses**: Super Admin melihat semua (teknis + operasional), Admin Yayasan hanya melihat yang operasional.

## Aturan Akses

| Kategori | Super Admin | Admin Yayasan |
|---|---|---|
| Branding, tema, homepage, sosmed, kontak, footer, SEO default | ✓ | ✓ (khusus tenant-nya) |
| Kredensial Midtrans per tenant | ✓ | ✗ |
| Email/SMTP | ✓ | ✗ |
| Domain/SSL | ✓ | ✗ |
| Sistem (timezone, locale, currency, maintenance) | ✓ | ✗ |
| Backup | ✓ | ✗ |
| **Pilihan template** | ✓ | ✓ (bisa pilih sendiri) |

## Kategori Konfigurasi

### Operasional (visible ke Admin Yayasan)

| Field | Keterangan |
|---|---|
| Branding | Nama situs, tagline, logo, favicon |
| Tema | Warna utama/aksen, font |
| Homepage | Hero section (judul, deskripsi, background), urutan & tampilan section |
| Sosial media | Link FB, IG, X, YouTube, WhatsApp |
| Kontak | Alamat, email, telepon, embed maps |
| Footer | Teks, tautan, copyright |
| SEO default | Meta title, meta description, og image |

### Teknis (Super Admin only)

| Field | Keterangan |
|---|---|
| Payment gateway | Kredensial Midtrans, environment (sandbox/production) |
| Email/SMTP | Konfigurasi mailer |
| Domain/SSL | Kelola custom domain, status SSL |
| Sistem | Timezone, locale, mata uang, maintenance mode |
| Backup | Jadwal & penyimpanan backup |

## Keputusan Kunci
- **Kredensial payment per tenant** — tiap yayasan punya akun Midtrans sendiri; dana donasi masuk langsung ke rekening yayasan. Kredensialnya (server key, client key, environment) dikonfigurasi Super Admin sebagai inputan teknis, disembunyikan dari Admin Yayasan.
- **Beberapa pilihan template** — yayasan bisa pilih template public site (default + alternatif). Tiap template punya slot customisasi warna/font/section.

## Data Model

| Entitas | Keterangan |
|---|---|
| `website_configs` (per tenant) | Branding, tema, homepage, sosmed, kontak, footer, SEO default, template terpilih |
| `technical_configs` (per tenant, hidden) | `midtrans_server_key`, `midtrans_client_key`, `midtrans_env`, SMTP, domain/SSL, dsb — hanya Super Admin |
| `templates` (platform level) | Daftar template public site yang tersedia |

## Keterkaitan
- [Modul 02 — Tenant](./02-tenant.md): branding dasar; di sini versi lengkap konfigurasi website.
- [Modul 05 — Donation](./05-donation.md): kredensial Midtrans per tenant dipakai di sini.
- [Modul 15 — GTM/GA4](./15-gtm-ga4.md): modul tersendiri (bukan bagian dari sini).