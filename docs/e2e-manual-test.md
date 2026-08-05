# Manual End-to-End Test — Platform Donasi Yayasan

Dokumen tahapan **End-to-End (E2E) Test manual** untuk memverifikasi seluruh alur platform (public website + donasi + admin panel + platform super admin) berjalan utuh dari sisi pengguna nyata (browser).

> [Kembali ke navigasi utama](../README.md)

---

## 1. Tujuan

1. Memastikan seluruh alur inti (browse → donasi → pembayaran → laporan) berfungsi dari browser sungguhan.
2. Memverifikasi multi-tenant: 2 yayasan, admin masing-masing, dan super admin platform.
3. Memvalidasi integrasi pembayaran **Midtrans Sandbox** (Snap popup, webhook, status).
4. Memvalidasi insight: tracking UTM, funnel, laporan per tenant & lintas tenant.
5. Menangkap cacat fungsional sebelum produksi.

---

## 2. Prasyarat & Setup

### 2.1 Hosts (`C:\Windows\System32\drivers\etc\hosts`)

```
127.0.0.1 yayasan-go-digital.test
127.0.0.1 kerkomit.test
127.0.0.1 yayasan-nusantara.test
```

> `yayasan-go-digital.test` = domain utama platform (landing + admin super admin, TANPA tenant).
> `kerkomit.test` = tenant 1 (Yayasan Kerkomit).
> `yayasan-nusantara.test` = tenant 2 (dari dummy seeder).

### 2.2 Jalankan Aplikasi

Gunakan Laragon (atau `php artisan serve --host=127.0.0.1 --port=80` — butuh hak admin agar port 80 jalan). Pastikan MySQL aktif dan `.env` berisi `MIDTRANS_IS_PRODUCTION=false`.

### 2.3 Seed Data

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=E2eDummySeeder
```

`E2eDummySeeder` menambahkan (lihat §3): 6 donasi paid + 4 donasi status lain, 4 leads, 10 page visits, short link tetap `E2EBEA`, GTM aktif, dan tenant 2 **Yayasan Nusantara Sejahtera** dengan 2 donasi paid.

### 2.4 Akun Uji

| Peran | Email | Password | Tenant |
|---|---|---|---|
| Super Admin | `superadmin@system.test` | `password` | Platform (tidak ada) |
| Admin Yayasan | `admin@kerkomit.test` | `password` | kerkomit |
| Admin Yayasan | `admin@yayasan-nusantara.test` | `password` | yayasan-nusantara |

---

## 3. Dummy Data Reference

Data statis yang sengaja dibuat tetap agar mudah diverifikasi di UI:

| Item | Nilai |
|---|---|
| Short link tetap | `http://kerkomit.test/go/E2EBEA` → campaign `beasiswa-batch-2026` (UTM google/cpc) |
| Order ID donasi paid | `E2E-DONA-0001` … `E2E-DONA-0006` (total paid kerkomit = **Rp 1.625.000**) |
| Order ID status lain | `E2E-DONA-0101` pending, `E2E-DONA-0102` failed, `E2E-DONA-0103` expired, `E2E-DONA-0104` refunded |
| Donasi anonim | `E2E-DONA-0004` (`is_anonymous = true`) → tampil sebagai "Hamba Allah" |
| GTM / GA4 | `GTM-TEST123` / `G-TEST123456` (status **active**) |
| Tenant 2 | `yayasan-nusantara.test`, order `NUS-DONA-0001` (Rp 500.000) & `NUS-DONA-0002` (Rp 7.000.000) → total tenant 2 = **Rp 7.500.000** |
| Agregat platform (paid) | Kerkomit Rp 1.625.000 + Nusantara Rp 7.500.000 = **Rp 9.125.000** |
| Leads | `rina.m@example.com` (new), `sinta.d@example.com` (new), `tono.h@example.com` (processing), `umi.k@example.com` (closed) |
| Campaign | `beasiswa-batch-2026` (target Rp 150.000.000, active), `dana-darurat-beasiswa`, `bantuan-gempa-cianjur` (completed) |
| Page visits | 10 record, device mobile/desktop/tablet, sumber google/instagram/whatsapp/direct |

### 3.1 Kartu Uji Midtrans Sandbox

| Skenario | Kartu | Keterangan |
|---|---|---|
| Sukses (3DS) | `4811 1111 1111 1114` | CVV `123`, tanggal kadaluarsa masa depan, OTP `112233` |
| Gagal / decline | kartu selain di atas (mis. `4911 1111 1111 1113`) | simulasi pembayaran ditolak |
| Bank transfer VA | menu **Bank Transfer → BCA VA** | ikuti instruksi sandbox; **tidak perlu transfer sungguhan** |

> Sumber: https://docs.midtrans.com — daftar lengkap kartu sandbox.

---

## 4. Tata Cara Pelaksanaan

- Kerjakan berurutan (alur saling bergantung).
- Tulis hasil di kolom **Status** tiap kasus: `PASS` / `FAIL` / `N/A` + **Catatan** bila FAIL (langkah yang gagal, pesan error, screenshot).
- Setiap FAIL → selesaikan sebelum lanjut ke tahap berikut.

---

## 5. Skenario Uji

### T1 — Persiapan & Setup

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T1.1 | Jalankan `migrate:fresh --seed` + `E2eDummySeeder` tanpa error | Command sukses; data tercatat (§3) | |
| T1.2 | Buka `http://kerkomit.test` | Halaman beranda tenant Kerkomit tampil | |
| T1.3 | Buka `http://yayasan-nusantara.test` | Beranda tenant Nusantara tampil (konten beda dari Kerkomit) | |
| T1.4 | Buka `http://yayasan-go-digital.test` | Landing/platform (tanpa tenant) tampil, bukan 404 | |
| T1.5 | Buka `http://yayasan-go-digital.test/sitemap.xml` | **404** (domain utama tanpa tenant) | |

### T2 — Auth & RBAC

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T2.1 | Buka `http://kerkomit.test/admin/login`, login `admin@kerkomit.test` / `password` | Masuk ke Dashboard admin Kerkomit | |
| T2.2 | Buka `http://kerkomit.test/admin/dashboard` | Sidebar: Dashboard, Program, Campaign, Artikel, Album, Pengurus, Halaman, Donasi, Campaign Link, Leads, Media, User, Pengaturan, Laporan, Audit Log, Backup, GTM | |
| T2.3 | Logout (tombol keluar) | Kembali ke halaman login | |
| T2.4 | Login `superadmin@system.test` di `http://yayasan-go-digital.test/admin/login` | Masuk; menu berisi **Tenants** + **Laporan** lintas tenant | |
| T2.5 | Login `admin@yayasan-nusantara.test` di `http://yayasan-nusantara.test/admin/login` | Dashboard Nusantara; data hanya milik Nusantara | |
| T2.6 | (Opsional 2FA) Aktifkan 2FA di menu setup user | Proses TOTP aktif; login berikutnya minta kode | |

### T3 — Public Site: Navigasi & Home

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T3.1 | Beranda `kerkomit.test` | Hero, program unggulan, campaign terbaru, berita, CTA donasi tampil | |
| T3.2 | Klik menu Program | `/programs` menampilkan program tersedia (`beasiswa-anak-yatim`, `tanggap-bencana`) | |
| T3.3 | Buka `/program/beasiswa-anak-yatim` | Detail program + CTA donasi + artikel terkait | |
| T3.4 | Buka `/campaigns` | Daftar campaign dengan progress | |
| T3.5 | Buka `/campaign/beasiswa-batch-2026` | Detail campaign: story, progress Rp, jumlah donatur, tombol **Donasi Sekarang** | |
| T3.6 | Buka `/articles`, lalu `/article/serah-terima-beasiswa-2026` | List & detail artikel; views bertambah | |
| T3.7 | Buka `/albums` → detail album | Galeri foto tampil | |
| T3.8 | Buka `/pengurus` | Struktur pengurus (Pembina/Pengawas/Pengurus/Anggota) | |
| T3.9 | Buka `/page/tentang` (atau halaman statis lain) | Halaman statis tampil | |
| T3.10 | Buka `http://yayasan-nusantara.test` | Beranda Nusantara tampil dengan program `bimbel-gratis` | |

### T4 — Donasi: Alur Lengkap (KRITIS)

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T4.1 | Buka `/donasi/beasiswa-batch-2026` | Form donasi tampil (nama, email, HP, nominal, pesan, anonim) | |
| T4.2 | Isi nominaal **50.000**, nama/email/HP valid, lalu **submit** | Popup **Snap.js Midtrans Sandbox** terbuka (token flash) | |
| T4.3 | Pilih **Credit Card**, isi `4811 1111 1111 1114`, CVV `123`, exp masa depan, klik Pay, isi OTP `112233` | Pembayaran sukses; redirect ke halaman status donasi | |
| T4.4 | Halaman status menampilkan **Berhasil/Paid** | Status `paid`; `donation_completed` di dataLayer GTM | |
| T4.5 | Ulangi T4.2–T4.3 tapi pilih **Bank Transfer → BCA VA** | Virtual account ditampilkan (tidak perlu bayar) | |
| T4.6 | Ulangi donasi nominal < minimum | Validasi menolak (tampilkan pesan error) | |
| T4.7 | Isi form kosong / email salah | Validasi memblokir submit | |
| T4.8 | Donasi dengan centang **Anonim** | Di daftar donatur publik tampil "Hamba Allah", bukan nama asli | |

### T5 — Webhook & Pembaruan Status

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T5.1 | (Dev) Kirim notifikasi manual ke `POST /payment/webhook/midtrans` dengan order_id `E2E-DONA-0101` status `settlement` (tanpa CSRF) | Status donasi berubah `pending` → `paid`; signature diverifikasi | |
| T5.2 | Cek di Admin → Donasi | Donasi `E2E-DONA-0101` tampil **paid**, `paid_at` terisi | |
| T5.3 | Admin ubah status donasi `E2E-DONA-0102` (failed) → paid manual via tombol status | Status berubah; tercatat di audit log | |

### T6 — Campaign Link & Shortlink (UTM)

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T6.1 | Buka `http://kerkomit.test/go/E2EBEA` | Karena GTM aktif: halaman antara tampil (200) dgn push `campaign_link_click` ke dataLayer lalu auto-redirect ke `/campaign/beasiswa-batch-2026?...utm_source=google`. (Jika GTM nonaktif → 302 langsung) | |
| T6.2 | Buka Admin → Campaign Link | Link `E2EBEA` / "Google Ads E2E" tampil dengan clicks_count > 0 | |
| T6.3 | Klik tautan `Bio Instagram` dari CampaignLinkSeeder | Redirect ke campaign dengan `utm_source=instagram` | |
| T6.4 | Donasi lewat link UTM Instagram (`E2E-DONA-0001`) | Di Admin → Donasi, field UTM terisi `instagram` | |
| T6.5 | Buka `/go/ABCD12` (kode tak dikenal) | 404 | |

### T7 — Kontak & Leads

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T7.1 | Buka `/kontak`, isi form, submit | Sukses; `lead_submitted` di dataLayer; muncul notifikasi sukses | |
| T7.2 | Admin → Leads | Lead baru tampil status `new` (email/whatsapp) | |
| T7.3 | Admin buka detail lead, ubah status → `processing` | Status tersimpan; audit log tercatat | |
| T7.4 | Hapus salah satu lead (dummy) | Lead terhapus (soft delete); audit log tercatat | |

### T8 — Admin Panel: CRUD Konten

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T8.1 | Admin → Program → Tambah Program | Program baru tampil di public `/programs` | |
| T8.2 | Admin → Campaign → Tambah Campaign | Campaign baru tampil di public `/campaigns` | |
| T8.3 | Admin → Artikel → Buat draft → publish | Artikel tampil di `/articles`; draft TIDAK tampil | |
| T8.4 | Admin → Album → Tambah + upload foto via gallery | Galeri publik menampilkan foto | |
| T8.5 | Admin → Pengurus → Tambah anggota | Tampil di `/pengurus` | |
| T8.6 | Admin → Halaman → Edit `tentang` | Perubahan tampil di `/page/tentang` | |
| T8.7 | Setiap aksi tambah/ubah/hapus di atas | Tercatat di **Audit Log** (T12) | |

### T9 — Admin Panel: Media & Pengaturan

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T9.1 | Admin → Media → Upload 1 gambar | Varian thumbnail/medium/large dibuat; preview tampil | |
| T9.2 | Media → cari & filter kategori | Hasil sesuai | |
| T9.3 | Admin → Pengaturan → ubah nama/deskripsi yayasan | Tampil di beranda public | |

### T10 — Admin Panel: GTM/GA4

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T10.1 | Admin Kerkomit buka `/admin/gtm` | **403** (admin yayasan tidak punya `tenant.edit`) | |
| T10.2 | Super Admin buka `yayasan-go-digital.test/admin/gtm` | Form GTM tampil; pilih tenant `kerkomit` | |
| T10.3 | Super Admin set `GTM-TEST123` status **active**, simpan | Tersimpan; cache di-refresh | |
| T10.4 | Buka source halaman public Kerkomit (Ctrl+U) | Snippet GTM `GTM-TEST123` + `dataLayer=[]` muncul; event `article_view`/`program_view` terpasang | |
| T10.5 | Buka halaman public Nusantara | Tanpa snippet GTM (belum dikonfigurasi) | |

### T11 — Admin Panel: Laporan & Insight (Tenant)

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T11.1 | Admin Kerkomit buka `/admin/reports` | Statistik: donasi masuk 10, berhasil 6, terkumpul **Rp 1.625.000**, donatur unik, program, campaign, kunjungan, leads 4, views konten | |
| T11.2 | Cek blok **Funnel Konversi** | Kunjungan → donasi dibuat → sukses + persentase konversi tampil | |
| T11.3 | Cek **Donasi per Campaign** | Kolom Target & Progress terisi untuk `beasiswa-batch-2026` | |
| T11.4 | Cek **Channel Atribusi (UTM)** | Sumber `instagram`, `google`, `whatsapp`, `email` dgn jumlah & total | |
| T11.5 | Cek **Metode Pembayaran** | `qris`, `bank_transfer`, `virtual_account`, `ewallet` dgn jumlah | |
| T11.6 | Filter tanggal `from`/`to` | Data menyempit sesuai rentang | |
| T11.7 | Cek **Tren 12 bulan**, perangkat, top pages, status leads | Semua blok tampil dengan data | |
| T11.8 | Login Staff (buat user staff di admin) lalu buka `/admin/reports` | **403** | |

### T12 — Audit Log

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T12.1 | Admin Kerkomit buka `/admin/audit-logs` | Log hanya milik tenant Kerkomit | |
| T12.2 | Buat/edit/hapus sebuah Program | Muncul log `create`/`update`/`delete` dgn old & new values | |
| T12.3 | Login & logout | Tercatat aksi `login` / `logout` | |
| T12.4 | Super Admin buka `yayasan-go-digital.test/admin/audit-logs` | Melihat log **seluruh tenant** (termasuk Nusantara) | |

### T13 — Backup & Restore

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T13.1 | Admin Kerkomit buka `/admin/backups` → **Buat Backup** | File backup JSON dibuat | |
| T13.2 | Unduh backup | File terunduh (isi JSON per tenant) | |
| T13.3 | Ubah sebuah data, lalu **Restore** backup | Data kembali seperti saat backup | |
| T13.4 | (Opsional) Jalankan `php artisan backup:run --scope=platform` | Backup platform dibuat; terjadwal harian 02:00 | |

### T14 — Platform: Super Admin (Tenant & Insight)

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T14.1 | Super Admin buka `/admin/tenants` | Daftar 2 tenant + status | |
| T14.2 | Tambah tenant baru (uji) | Tenant baru bisa dipakai, subdomain baru | |
| T14.3 | Ubah status tenant ke **inactive** → buka subdomain-nya | **404** "Domain tidak ditemukan" | |
| T14.4 | Buka `/admin/reports` sebagai super admin | Blok **Performa Yayasan**: Kerkomit & Nusantara; terkumpul platform **Rp 9.125.000** | |
| T14.5 | Cek funnel & channel di level platform | Data agregat seluruh tenant | |
| T14.6 | Gunakan tenant switcher (jika tersedia) untuk membuka admin sebagai tenant | Konteks berpindah; menu/laporan berubah per tenant | |

### T15 — Keamanan & Edge Case

| ID | Langkah | Hasil yang diharapkan | Status |
|---|---|---|---|
| T15.1 | Akses `/admin/*` tanpa login | Redirect ke `/login` | |
| T15.2 | Staff coba akses Donasi/Media/GTM | 403 sesuai permission | |
| T15.3 | Buka `/go/kode-salah` | 404 | |
| T15.4 | Donasi pakai nominal 0 / negatif | Ditolak validasi | |
| T15.5 | Email duplikat saat daftar user | Error validasi | |
| T15.6 | Buka domain tak dikenal | 404 | |

---

## 6. Ringkasan Hasil

| Kelompok | Total | PASS | FAIL | N/A |
|---|---|---|---|---|
| T1 — Setup | 5 | | | |
| T2 — Auth/RBAC | 6 | | | |
| T3 — Public navigasi | 10 | | | |
| T4 — Donasi | 8 | | | |
| T5 — Webhook/status | 3 | | | |
| T6 — Shortlink/UTM | 5 | | | |
| T7 — Leads | 4 | | | |
| T8 — CRUD konten | 7 | | | |
| T9 — Media/pengaturan | 3 | | | |
| T10 — GTM/GA4 | 5 | | | |
| T11 — Laporan tenant | 8 | | | |
| T12 — Audit log | 4 | | | |
| T13 — Backup | 4 | | | |
| T14 — Platform | 6 | | | |
| T15 — Keamanan | 6 | | | |
| **TOTAL** | **84** | | | |

## 7. Keputusan

- [ ] **LOLOS** — semua kasus PASS atau FAIL yang dianggap minor & sudah berkoordinasi dengan tim.
- [ ] **TIDAK LOLOS** — ada FAIL mayor → laporkan detail ke tim pengembang sebelum rilis.

---

## Catatan Penguji

Tanggal uji: ________________  Penguji: ________________  Lingkungan: ________________
