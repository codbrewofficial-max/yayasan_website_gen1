# Modul 14 — Leads WhatsApp & Kontak

## Tujuan
Menangkap dan mencatat semua orang yang menghubungi yayasan lewat **Email** atau **WhatsApp** sebagai *lead*, dengan form input di public site.

## Alur

```
Pengunjung → form kontak (nama, email/HP, topik, pesan)
   ├── Hubungi via Email   → backend: simpan lead + kirim email ke yayasan (berisi input) → status new
   └── Hubungi via WhatsApp → backend: simpan lead + redirect ke wa.me (pesan carry input) → status new

Dashboard lead (Admin Yayasan + Super Admin):
   update status → filter → riwayat
```

## Keputusan Kunci
- **Kirim email + simpan lead** — karena form submit ke backend dulu, maka (1) outbound (email / redirect WhatsApp), (2) lead selalu tercatat di DB. Kontak tidak pernah hilang meski dikirim ke email eksternal.
- **Ada status pengelolaan** — `new`, `processing`, `closed` untuk tracking follow-up.

## Struktur Data Inti (`leads`)

| Field | Keterangan |
|---|---|
| `tenant_id` | Scoping |
| `name` | Nama pengunjung |
| `email` | Email pengunjung |
| `phone` | Telepon/WA pengunjung |
| `subject` / `topic` | Subjek / kategori |
| `message` | Isi pesan |
| `lead_type` | `email` / `whatsapp` (jalur yang dipilih) |
| `status` | `new` / `processing` / `closed` |
| `created_at` | Waktu input |
| `updated_at` | Untuk follow-up |

## Perilaku yang Diperhatikan
- **Email**: data lead tetap tersimpan di sistem (backend handle submission sebelum kirim email) — dashboard tetap bisa mengelola rekap meski email masuk ke kotak email yayasan.
- **WhatsApp**: data dicatat sebelum redirect ke wa.me dengan isi pesan terisi.

## Dashboard Internal
- Daftar lead dengan filter (jalur, status, tanggal).
- Update status `new → processing → closed`.
- Riwayat lead.

## Keterkaitan
- [Modul 12 — Website Configuration](./12-website-configuration.md): konfigurasi kontak & readiness pengaturan email.
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): akses (Super Admin & Admin Yayasan).