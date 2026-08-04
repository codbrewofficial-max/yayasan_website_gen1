# Admin Panel Architecture — Platform Donasi Yayasan (Yayasan Website Gen1)

Dokumen arsitektur admin panel berdasarkan keputusan diskusi:
- Teknologi: Blade + Livewire + **AdminLTE** (template gratis) + komponen Livewire siap pakai
- Menu: sidebar **dinamis per role** (Platform vs Tenant) + filter by permission
- Tenant switcher untuk Super Admin (berpindah konteks antar yayasan & mode platform)

---

## Ringkasan Keputusan

| Aspek | Keputusan |
|---|---|
| Teknologi | Blade + Livewire |
| Template | AdminLTE (free) + TallStack UI / WireUI (komponen Livewire) |
| Struktur menu | Sidebar dinamis per role/permission |
| Tenant switcher | Ya (Super Admin berpindah antar yayasan) |
| Jumlah panel | 1 (dibangun sekali, reusable) |
| Role | Super Admin (platform+semua), Admin Yayasan (tenant), Staff (tenant, terbatas) |
| Donatur | Tidak masuk admin panel (UI riwayat di front) |

---

## 1. Peran Admin Panel

Satu admin panel melayani semua role internal:

```
Satu Admin Panel → semua role
├─ Super Admin    → semua tenant + platform (dashboard lintas)
├─ Admin Yayasan  → tenant-nya sendiri (full)
├─ Staff Yayasan  → tenant-nya (permission terbatas)
└─ Donatur        → TIDAK masuk admin (riwayat di front/public)
```

## 2. Teknologi

- **Blade + Livewire** (konsisten dengan stack frontend).
- **AdminLTE** — template layout Bootstrap gratis, matang, kompatibel Laravel/Livewire.
- **TallStack UI / WireUI** — komponen Livewire siap pakai (modal, form, table, alert) → hemat waktu develop.
- Mengikuti pola backend: Controller tipis + Service, global scope tenant, RBAC spatie.

## 3. Layout & Struktur UI

```
resources/views/admin/
├── layouts/
│   ├── app.blade.php        # layout AdminLTE (sidebar, topbar, content)
│   ├── sidebar.blade.php    # dinamis by role + permission
│   └── topbar.blade.php     # tenant switcher, notifikasi, user menu
├── platform/                # halaman mode Platform (Super Admin)
│   ├── dashboard.blade.php
│   ├── tenants.blade.php
│   ├── templates.blade.php
│   └── ...
├── tenant/                  # halaman mode Tenant (konteks switcher)
│   ├── dashboard.blade.php
│   ├── programs.blade.php
│   ├── campaigns.blade.php
│   ├── donations.blade.php
│   ├── articles.blade.php
│   ├── albums.blade.php
│   ├── members.blade.php
│   ├── media.blade.php
│   ├── leads.blade.php
│   ├── website-config.blade.php
│   ├── users.blade.php
│   └── audit.blade.php
└── livewire/                # komponen Livewire per halaman/aksi
```

- **Topbar**: tenant switcher (Super Admin), notifikasi, menu user.
- **Content**: slot per halaman (Livewire component atau Blade).

## 4. Sidebar Dinamis per Role

- Menu dirender dari **definisi terpusat** (nama, route, ikon, permission) → disaring sesuai role/permission user.
- **Group Platform** (Super Admin): dashboard lintas tenant, tenant management, templates, system config, audit (semua).
- **Group Tenant** (semua role tenant): dashboard, program/campaign, donasi, artikel, galeri, pengurus, media, leads, website config, users (Admin Yayasan), audit (tenant).
- Menu yang tidak diizinkan (permission tidak ada) tidak dirender.

## 5. Mode & Tenant Switcher

```
Super Admin:
  Mode Platform  → kelola lintas tenant (overview, tenant mgmt, templates, audit)
  Mode Tenant    → kelola tenant tertentu (konteks aktif via switcher)
  ├─ berpindah antar yayasan dengan switcher (topbar)
  └─ scope tenant mengikuti konteks aktif

Admin Yayasan / Staff:
  Hanya Mode Tenant, terkunci ke tenant-nya (dari pivot RBAC)
  Tanpa switcher
```

## 6. Menu per Area

### Platform (Super Admin)
| Menu | Fungsi |
|---|---|
| Overview lintas tenant | Total donasi, performa semua yayasan |
| Tenant Management | Antrian verifikasi, suspend, kill-switch, quota |
| Templates | Kelola template public site |
| System / Platform config | Teknis semua tenant, maintenance |
| Audit log (semua) | Pantau aktivitas sistem |

### Tenant (Admin Yayasan + Staff, konteks aktif)
| Menu | Fungsi |
|---|---|
| Dashboard | Statistik yayasan (donasi, campaign) |
| Program / Campaign | CRUD + link tracking |
| Donations | Detail transaksi, refund mark |
| Article | Kelola berita |
| Album / Gallery | Kelola galeri |
| Pengurus | Struktur organisasi |
| Media Library | Kelola aset |
| Leads | Kontak masuk (new/processing/closed) |
| Website Config | Operasional (branding, konten section, pilih template) |
| Users | Kelola staff + grant permission (Admin Yayasan) |
| Audit (tenant) | Pantau perubahan tenant-nya |

## 7. Hak Akses

| Role | Mode | Scope |
|---|---|---|
| Super Admin | Platform + Tenant (switcher) | Semua |
| Admin Yayasan | Tenant | Tenant-nya (full) |
| Staff | Tenant | Tenant-nya (permission terbatas) |

- Enforcement: spatie middleware `role`/`permission` + Policy per model + global scope `tenant_id`.

## 8. Integrasi dengan Modul Lain

- Donation flow & Midtrans webhook (handler di backend, admin tampil data transaksi).
- Media Library (pipeline webp) diakses dari form modul.
- Website Config: editor content sections (operasional) — teknis disembunyikan dari Admin Yayasan.
- Audit log: dashboard platform + tenant.

---

## Keterkaitan Dokumen Lain
- [Backend Architecture](./backend-architecture.md)
- [Infrastruktur & Stack](./infrastructure.md)
- [Roadmap MVP](./roadmap.md)
- [Public Site Architecture](./public-site-architecture.md)
- [Dokumentasi Modul](../modules/index.md)