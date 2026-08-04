# Roadmap MVP — Platform Donasi Yayasan (Yayasan Website Gen1)

Dokumen roadmap implementasi MVP, berdasarkan keputusan diskusi:
- Strategi eksekusi: **Backend → Public Website → Admin Panel** (siklus per fitur, monolith)
- Scope MVP: **Full 14 item** (Fase 0–3)
- Template public site: **on-demand** (template #1 = blueprint)

---

## Prinsip Pengerjaan

| Prinsip | Keterangan |
|---|---|
| Backend & Admin Panel | Dibangun **sekali**, reusable (1 saja) |
| Public Website | Bagian **paling banyak dikerjakan** (banyak template) |
| Template #1 | Bukan sekadar template — **blueprint/pola** agar template berikutnya mudah dibuat |
| Renderer inti vs template | Data loading & logic di renderer inti (sekali); template hanya tampilan (Blade/Livewire + config) |
| Template baru | **On-demand** — dibuat saat yayasan butuh tampilan beda |
| **Siklus per fitur** | Backend fitur → public fitur → review (Postman + browser) sebelum lanjut |
| **Fungsional MVP dulu** | Tiap fitur dikerjakan fungsional (backend+public+SEO) dulu; admin menyusul belakangan |
| **Review per fitur** | Setiap fitur di-review sebelum pindah ke fitur berikut |

---

## Urutan Eksekusi Detail

### Fase A — Fondasi (sekali, tanpa siklus)

| No | Item | Detail |
|---|---|---|
| A1 | Setup proyek | Laravel 11/12, PHP 8.3, MySQL, migrasi inti |
| A2 | Resolver tenant | Host header → tenant + global scope (`tenant_id`) |
| A3 | Auth + RBAC | Sanctum (token) + 2FA TOTP; spatie/permission (4 role tetap, scoped per tenant) |
| A4 | Media Library | Auto webp + multi-varian (< 100KB), folder + filter + search |

### Fase B — Siklus Fitur: Konten

Tiap fitur dikerjakan fungsional MVP (backend + public + SEO) lalu di-review sebelum lanjut.

| No | Fitur | Alur |
|---|---|---|
| B1 | Program | Backend → public list/detail |
| B2 | Campaign | Backend → public list/detail |
| B3 | Article | Backend → public |
| B4 | Album + Gallery | Backend → public |
| B5 | Pengurus | Backend → public |

### Fase C — Donasi (core revenue)

| No | Fitur | Alur |
|---|---|---|
| C1 | Donation flow backend | Midtrans Snap, webhook + signature verification, status mapping — test Postman |
| C2 | Public donation flow | Halaman donasi + UI — cek browser end-to-end |
| C3 | Campaign links | Short link + UTM, link_clicks, statistik per link |

### Fase D — Pelengkap

| No | Fitur |
|---|---|
| D1 | Leads WA/kontak (form public → email/WA) |
| D2 | page_visits + views count |
| D3 | Halaman statis (tentang, faq, privasi, ketentuan) |

### Fase E — Admin Panel (lapis akhir, CRUD generik)

| No | Item |
|---|---|
| E1 | Layout AdminLTE + sidebar dinamis + tenant switcher |
| E2 | Admin per modul (CRUD generik) |
| E3 | Dashboard admin + donasi admin + link tracking UI |

### Fase F — Lengkap & Polish

| No | Item |
|---|---|
| F1 | Website config editor content sections |
| F2 | Audit log + backup |
| F3 | GTM/GA4 + insight platform |
| F4 | Template #1 polish + template on-demand |

---

## Aturan Main Eksekusi

- **Per fitur**: fungsional MVP dulu (backend + public + SEO), lalu **review** (Postman + browser) sebelum pindah fitur.
- **Admin menyusul di Fase E** untuk fitur yang sudah dibuat (kecuali dibutuhkan lebih awal).
- **Donasi** = prioritas diuji cepat (core revenue).
- Backend & public dikembangkan bersama per fitur (monolith), bukan proyek terpisah.

---

## Di Luar MVP (Fase Lanjutan)

| Item | Status |
|---|---|
| Recurring donation | Backlog fase 2 |
| GTM/GA4 | Modul tersedia, diaktifkan per tenant |
| Template tambahan | On-demand (baru dibuat saat diminta) |
| Insight lintas platform penuh | Modul 13 diperdalam |
| Custom domain self-service | Saat onboarding yayasan |

---

## Estimasi Relatif

Estimasi di bawah bersifat **relatif** (satuan kerja), bukan waktu kalender — disesuaikan SDM tim.

| Fase | Beban relatif |
|---|---|
| A — Fondasi | ++++ |
| B — Konten | +++ |
| C — Donasi | ++++ (krusial, diuji cepat) |
| D — Pelengkap | ++ |
| E — Admin Panel | +++ |
| F — Lengkap & polish | ++ |

> Public website (Fase B–D) adalah bagian yang terus dikerjakan dan paling berat. Template #1 (F4) harus didesain sebagai blueprint yang mudah ditiru.

## Keterkaitan Dokumen Lain
- [Dokumentasi Modul](../modules/index.md)
- [ERD Master](../erd/index.md)
- [Infrastruktur & Stack](./infrastructure.md)
- [Backend Architecture](./backend-architecture.md)
- [Public Site Architecture](./public-site-architecture.md)