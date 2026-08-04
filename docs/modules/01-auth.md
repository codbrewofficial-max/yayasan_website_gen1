# Modul 01 — Auth

## Tujuan
Menangani mekanisme masuk pengguna ke sistem: login, pembuatan sesi/token, verifikasi (2FA/OTP), dan reset password. Berbeda dengan Modul 11 (User Management) yang mengelola "siapa saja yang ada & punya hak apa".

## Role & Permission (4 Level)

| Role | Akses |
|---|---|
| **Super Admin** (Labkerkomit) | Full akses semua tenant + setting level infra/platform |
| **Admin Yayasan** | Akses penuh data yayasan sendiri, termasuk kelola Staff |
| **Staff Yayasan** | Akses terbatas by permission granular; default tanpa `donation.view` kecuali di-grant eksplisit Admin Yayasan |
| **Donatur** *(opsional)* | Akun ringan — lihat riwayat donasi sendiri; tetap bisa donasi tanpa akun (guest) |

Detail lebih lanjut tentang role, permission, dan penugasan ada di [Modul 11 — User Management + RBAC](./11-user-management-rbac.md).

## Mekanisme Auth

- **Token**: Sanctum Bearer token (konsep) untuk semua jenis user (Super Admin, Admin Yayasan, Staff, Donatur).
- **2FA (TOTP)**: wajib untuk Super Admin, opsional untuk Admin Yayasan.
- **Onboarding internal**: berbasis **undangan (invitation-based)**, bukan self-register, untuk Admin Yayasan & Staff.
- **Donatur**: bisa **self-register** (daftar sendiri) atau tetap **guest checkout** (satu-satunya role yang boleh daftar sendiri).

## Auth untuk Donatur (Opsional Login)

Karena login donatur bersifat opsional, ada 2 jalur yang berjalan berdampingan:

1. **Guest donation** — isi nama, email, atau nomor WA tanpa password; transaksi tercatat memakai email/HP sebagai identifier.
2. **Login/Daftar** — email + password atau **OTP via email/WA** (lebih ramah untuk donasi kecil, tidak perlu ingat password).

### Dampak ke Tabel Donation
- Kolom `user_id` **nullable** (kosong jika guest).
- Kolom `donor_email` / `donor_phone` selalu wajib (untuk kirim struk & notifikasi apapun statusnya).
- Jika donatur guest kemudian daftar akun dengan email yang sama → riwayat donasi lama bisa **di-klaim** ke akun baru (match by email).

## Fitur Tambahan
- Rate limiting.
- Login log dasar.
- Reset password standar.

## Keterkaitan
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): siapa yang ada & hak aksesnya.
- [Modul 10 — Media Library](./10-media-library.md): upload foto profil/avatar user.