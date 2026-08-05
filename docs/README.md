# Dokumentasi — Platform Donasi Yayasan (Yayasan Website Gen1)

Halaman navigasi utama seluruh dokumentasi proyek.

## Gambaran Proyek

Platform **managed / setengah SaaS** untuk yayasan: yayasan hanya "pakai", infra dipegang Labkerkomit.

- **Arsitektur**: 1 Backend (Laravel, single DB + `tenant_id` scoping) → 1 Admin Panel (role-based) → N Public Website (templated renderer)
- **Hosting**: cPanel Shared, develop Windows
- **Status**: fase desain (dokumentasi lengkap, implementasi belum dimulai)

## Navigasi Dokumen

### 1. Ringkasan & Arah
| Dokumen | Isi |
|---|---|
| [Roadmap MVP](./roadmap.md) | Fase pengembangan, item, prioritas, estimasi relatif |

### 2. Modul (17)
Dokumentasi fitur tiap modul (keputusan, data model, perilaku).

- [Index Modul](./modules/index.md) — daftar lengkap 17 modul
- Folder: [`docs/modules/`](./modules/)

### 3. ERD (Database)
Desain tabel & relasi.

- [ERD Master](./erd/index.md) — standar global, semua tabel & relasi
- Folder: [`docs/erd/`](./erd/) — 1 file ERD per modul

### 4. Arsitektur
| Dokumen | Isi |
|---|---|
| [Infrastruktur & Stack](./infrastructure.md) | Teknologi, hosting, deploy, domain, queue |
| [Backend Architecture](./backend-architecture.md) | Resolve tenant, global scope, RBAC, service layer |
| [Admin Panel Architecture](./admin-panel-architecture.md) | Layout, sidebar dinamis, tenant switcher |
| [Public Site Architecture](./public-site-architecture.md) | Templated renderer, daftar halaman final |

### 5. Business Logic
| Dokumen | Isi |
|---|---|
| [Business Logic](./business-logic.md) | Donation flow, Midtrans webhook, onboarding tenant |

### 6. UI/UX
| Dokumen | Isi |
|---|---|
| [UI/UX Wireframe](./ui-ux-wireframe.md) | Wireframe semua halaman public + content sections |

### 7. Pengujian
| Dokumen | Isi |
|---|---|
| [Manual End-to-End Test](./e2e-manual-test.md) | Tahapan uji E2E manual + referensi dummy data |

## Struktur Folder

```
docs/
├── README.md                          ← navigasi ini
├── roadmap.md
├── infrastructure.md
├── backend-architecture.md
├── admin-panel-architecture.md
├── public-site-architecture.md
├── business-logic.md
├── ui-ux-wireframe.md
├── e2e-manual-test.md                 ← tahapan uji E2E manual + dummy data
├── modules/                           ← 17 modul + index
└── erd/                               ← ERD master + 17 per modul
```

## Status Dokumen

| Area | Status |
|---|---|
| Modul (17) | ✅ Lengkap |
| ERD | ✅ Lengkap |
| Infrastruktur & Stack | ✅ Lengkap |
| Roadmap MVP | ✅ Lengkap |
| Backend Architecture | ✅ Lengkap |
| Admin Panel Architecture | ✅ Lengkap |
| Public Site Architecture | ✅ Lengkap |
| Business Logic | ✅ Lengkap |
| UI/UX Wireframe | ✅ Lengkap |

## Catatan

- Format & keputusan berdasarkan diskusi desain (konsep, teknologi: Laravel + Blade/Livewire + AdminLTE).
- Beberapa keputusan bertanda **TBD** menunggu finalisasi (misal daftar halaman statis dikembangkan seiring).
- Ubah dokumen ini jika menambah/mengganti file di `docs/`.
