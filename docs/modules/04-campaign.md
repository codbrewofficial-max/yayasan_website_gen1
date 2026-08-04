# Modul 04 — Campaign

## Tujuan
Instance **penggalangan dana spesifik** di bawah Program (misal "Campaign Beasiswa Batch 2026", "Campaign Gempa Cianjur Desember 2026"). Mencakup fitur **copy link ber-UTM dengan short link** untuk tracking & insight.

## Struktur Data Inti

| Field | Keterangan |
|---|---|
| `program_id` | Wajib, relasi ke Program induk |
| `title`, `slug` | SEO-friendly |
| `content` / `story` | Rich text — cerita/latar belakang campaign |
| `target_amount` | Target dana (nullable — model donasi terbuka tanpa target pasti) |
| `collected_amount` | Computed/cached dari sum `donations` |
| `start_date` | Tanggal mulai |
| `end_date` *(nullable)* | Null jika campaign open-ended |
| `status` | `draft`, `active`, `paused`, `completed`, `expired` |
| `featured_image`, gallery | Nyambung Media Library |
| `donation_type` | `one_time` saja, atau boleh juga `recurring` |
| `show_donor_list` | Toggle tampilkan daftar donatur di public page |
| `allow_anonymous` | Opsi donatur memilih "Hamba Allah"/anonim (nama tidak ditampilkan) |

## Fitur Copy Link dengan UTM Tracking

Membuat **link generator internal** per campaign, bukan sekadar link statis.

### Tabel `campaign_links`

| Field | Keterangan | Dipakai Insight untuk |
|---|---|---|
| `id` | Primary key | — |
| `campaign_id` | Link untuk campaign mana | Filter per campaign |
| `label` | Nama internal (misal "Bio Instagram", "Broadcast WA Grup A") | Pelabelan laporan |
| `utm_source` | instagram, facebook, whatsapp, email, dsb | **Analisis channel** |
| `utm_medium` | social, cpc, email, referral | Analisis medium |
| `utm_campaign` | Default auto-fill dari slug campaign, bisa di-override | Analisis campaign |
| `utm_content`, `utm_term` | Opsional, untuk A/B beda materi di channel sama | Analisis materi |
| `short_code` | Kode short link unik global (`Xk9dP2`) | Redirect |
| `target_url` | URL lengkap campaign + UTM yang di-generate | Redirect |
| `clicks_count` | **Cache** jumlah klik | Statistik ringkas |
| `last_clicked_at` | **Cache** waktu klik terakhir | Deteksi link mati |
| `created_by` | User yang generate | Audit |
| `created_at`, `updated_at` | Timestamps | — |

> `clicks_count` & `last_clicked_at` adalah **agregat/cache** dari tabel `link_clicks`, bukan sumber data utama.

### Tabel `link_clicks` (log per klik — fondasi tren per waktu)

| Field | Keterangan | Dipakai Insight untuk |
|---|---|---|
| `id` | Primary key | — |
| `campaign_link_id` | Relasi ke campaign_links | Join laporan |
| `referrer` | Asal pengunjung | Analisis sumber trafik |
| `device_type` | mobile / desktop / tablet | Analisis perangkat |
| `clicked_at` | Waktu klik | **Grafik tren klik per waktu** |

## Short Link — Infrastruktur

- Menggunakan **1 domain pendek terpusat** milik Labkerkomit (misal `dn.id`), bukan subdomain tiap yayasan — lebih mudah diingat, satu SSL untuk semua, redirect service terpusat.

### Flow Redirect
```
User klik dn.id/Xk9dP2
→ Controller cari short_code → log klik + increment clicks_count
→ 302 redirect ke target_url (URL campaign asli + UTM)
→ Browser landing di halaman campaign, JS capture UTM ke cookie/session
```

## Atribusi ke Donasi

- UTM params disimpan ke **session/cookie** (bukan hanya diteruskan ke Google Analytics).
- Saat pengunjung lanjut donasi (bisa beberapa saat kemudian selama session belum expired), data UTM ikut tersimpan di record `donations` (`utm_source`, `utm_medium`, `utm_campaign`, `campaign_link_id`).
- Ini memungkinkan laporan konversi per channel (lihat [Modul 13 — Tracking & Insight](./13-tracking-insight.md)).

## Statistik di Halaman Campaign

Halaman Campaign memiliki **tab/section "Link Tracking"** dengan tabel:

| Label Link | Short Link | Klik | Donasi Masuk | Total Rp | Konversi |
|---|---|---|---|---|---|
| Bio Instagram | dn.id/Xk9dP2 | 45 | 8 | Rp 3.200.000 | 17.8% |
| Broadcast WA | dn.id/Ab7xQ1 | 12 | 6 | Rp 9.500.000 | 50% |

Data dihitung dari join `campaign_links` ke `donations` (via `campaign_link_id`).

## Keterkaitan
- [Modul 05 — Donation](./05-donation.md): donasi terikat ke campaign & atribusi UTM.
- [Modul 03 — Program](./03-program.md): induk campaign.
- [Modul 13 — Tracking & Insight](./13-tracking-insight.md): agregasi lintas campaign/tenant.