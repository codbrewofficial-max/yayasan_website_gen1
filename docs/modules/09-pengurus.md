# Modul 09 — Pengurus/Anggota Yayasan

## Tujuan
Menampilkan **struktur organisasi** yayasan di public site (halaman "Pengurus" / "Struktur Organisasi") — siapa yang memimpin dan mengelola yayasan. Ini sinyal penting untuk kepercayaan donatur.

## Keputusan Kunci
- **Terpisah dari akun login** — murni data profil publik, tidak terhubung ke User Management (Modul 11). Perubahan akun staff tidak otomatis mengubah profil pengurus di website (dan sebaliknya).
- **Ada pengelompokan jabatan** — tampil terstruktur, bukan daftar flat.

## Struktur Data Inti (`pengurus` / `members`)

| Field | Keterangan |
|---|---|
| `name` | Nama lengkap |
| `group` | Pengelompokan: `pembina`, `pengawas`, `pengurus_inti`, `anggota` |
| `position` | Jabatan spesifik (Ketua, Sekretaris, Bendahara, dsb) |
| `photo` | Media Library |
| `bio` *(opsional)* | Deskripsi singkat |
| `sort_order` | Urutan tampil dalam grup |
| `status` | `active` / `inactive` (tampil atau disembunyikan) |
| `joined_at` *(opsional)* | Tahun mulai menjabat |

## Perilaku di Public Site
- Section per grup jabatan: **Pembina → Pengawas → Pengurus Inti → Anggota**, masing-masing berisi grid kartu (foto, nama, jabatan).
- Urutan dalam tiap grup diatur lewat `sort_order`.

## Keterkaitan
- [Modul 10 — Media Library](./10-media-library.md): foto profil pengurus.
- [Modul 11 — User Management + RBAC](./11-user-management-rbac.md): **tidak terhubung** (profil publik murni).