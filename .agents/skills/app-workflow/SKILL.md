---
name: app-workflow
description: Workflow and UX rules for building the family finance web app tailored for an absolute beginner wife.
---

# SKILL - Aplikasi Keuangan Keluarga (Buat Istri)

## Konteks & Persona
- **User Utama:** Istri, TIDAK PERNAH mencatat keuangan, bukan akuntan, bukan programmer.
- **Tujuan Jangka Panjang:** Menjadi aset & catatan digital keluarga yang bertahan 10+ tahun walau pembuatnya sudah tiada.
- **Bahasa UI:** Bahasa Indonesia sehari-hari, santai, ramah. **Dilarang keras** memakai istilah teknis akuntansi (debit, kredit, chart of accounts, double-entry, rekonsiliasi).

## Tech Stack & Arsitektur
- **Backend:** Laravel 13 (PHP 8.3+) + SQLite (1 file database, portabel & mudah dibackup).
- **Frontend:** Livewire 4 + Alpine.js + Tailwind CSS 4 + Flux UI.
- **PWA:** `vite-plugin-pwa` untuk instalasi *standalone app* di HP (fullscreen tanpa address bar).
- **Auth:** Session permanen (Remember Me panjang / 1 tahun) atau PIN 4-digit. Jangan biarkan user sering ter-logout.
- **Koneksi (V1):** Online-First. Utamakan kestabilan Livewire dengan server sebelum mencoba antrean offline sinkronisasi yang rumit.

## Prinsip UX (Hukum Utama)
1. **1 Halaman Utama (SPA Feel):** 90% aktivitas dilakukan di 1 halaman tanpa reload.
2. **Input 10 Detik:**
   - 3 input utama: Nominal, Kategori, Akun Sumber.
   - Tanggal default otomatis "Hari Ini".
   - Keterangan/catatan sifatnya opsional.
   - Tombol cepat nominal: tombol `+000`, `+10rb`, `+50rb`, `+100rb`.
   - Kategori berupa tombol/chips visual (bukan dropdown `<select>`), 1 kali tap langsung pilih.
3. **Ergonomi Mobile (Jempol-Friendly):**
   - Atas: Total saldo keluarga & kartu saldo tiap akun (BCA, GoPay, Dompet Tunai, dll.) bisa di-swipe horizontal.
   - Tengah: Filter ringkas (Hari Ini, Minggu Ini, Bulan Ini) & List Transaksi terbaru.
   - Bawah: Tombol aksi utama (FAB / Bottom Sheet) yang mudah dijangkau satu tangan untuk catat Pengeluaran (Merah), Pemasukan (Hijau), atau Pindah Saldo (Biru).
4. **No Guilt / Tanpa Tekanan:** Jangan ada notifikasi atau warna merah menyeramkan yang membuat istri merasa "bersalah" saat belanja.
5. **Konvensi Hutang Piutang (Ramah Istri):**
   - Dilarang memakai istilah kaku akuntansi seperti "Piutang / Accounts Receivable".
   - Gunakan istilah:
     - 🟢 **"Dipinjam Orang"** (Uang kita yang dipinjam teman/saudara, menunggu dikembalikan).
     - 🔴 **"Hutang Kita"** (Uang yang kita pinjam dan perlu kita kembalikan).
   - Terintegrasi langsung dengan mutasi kas (saat pinjamkan uang: saldo rekening berkurang; saat uang kembali: saldo bertambah).
   - Mendukung pelunasan langsung maupun cicilan bertahap tanpa pusing.
6. **Dilarang di V1:** Grafik rumit, split bill, multi-currency, foto struk OCR, budgeting/target hemat ketat.

## Database Schema (Minimalis & Praktis)
- `accounts`: `id`, `nama`, `saldo_awal` (bigint), `warna`, `icon`, `timestamps`
- `categories`: `id`, `nama`, `tipe` (`keluar` / `masuk`), `icon`, `urutan`, `timestamps`
- `transactions`:
  - `id`
  - `type`: `keluar` | `masuk` | `transfer`
  - `account_id` (FK accounts - sumber dana)
  - `target_account_id` (FK accounts, nullable - khusus jika type = `transfer`)
  - `category_id` (FK categories, nullable jika type = `transfer`)
  - `nominal` (bigint, selalu bernilai positif)
  - `keterangan` (string, nullable)
  - `tanggal` (date, default today)
  - `timestamps`
- `debts`:
  - `id`
  - `type`: `dipinjam_orang` | `kita_pinjam`
  - `nama_orang`: string
  - `nominal_total`: bigint
  - `keterangan`: string, nullable
  - `jatuh_tempo`: date, nullable
  - `status`: `belum_lunas` | `lunas`
  - `timestamps`
- `debt_payments`:
  - `id`
  - `debt_id`: FK debts
  - `account_id`: FK accounts
  - `transaction_id`: FK transactions, nullable
  - `nominal`: bigint
  - `tanggal`: date
  - `catatan`: string, nullable
  - `timestamps`

### Aturan Perhitungan Saldo
Saldo akun **TIDAK DISIMPAN** sebagai angka statis di tabel `accounts`. Saldo dihitung secara dinamis untuk mencegah korupsi data:
- `Saldo Akun = saldo_awal + SUM(masuk) - SUM(keluar) + SUM(transfer_masuk) - SUM(transfer_keluar)`

## Fitur Wajib V1
- [ ] Kartu saldo tiap akun/dompet (Total Saldo Keluarga + rincian per rekening).
- [ ] Catat Pengeluaran & Pemasukan super cepat (< 10 detik).
- [ ] Fitur "Pindah Saldo / Tarik Tunai" (misal: BCA ke Dompet Tunai / GoPay) dengan 1 input form.
- [ ] Fitur "Hutang & Pinjaman" ramah istri (Dipinjam Teman / Hutang Kita, bisa cicil/lunas & sinkron mutasi kas).
- [ ] Edit & hapus transaksi langsung di tempat via Modal / Inline.
- [ ] Filter sederhana: Hari Ini / Minggu Ini / Bulan Ini + filter per Akun/Kategori.
- [ ] Ekspor ke CSV / Excel 1-klik langsung unduh ke HP.
- [ ] PWA Standalone (bisa di-*Add to Home Screen* di iOS Safari & Android Chrome).

## Aturan Warisan Digital & Keberlanjutan
- **1-File SQLite Backup:** Database cukup di-copy 1 file `.sqlite`.
- **Automated Backup:** Jadwalkan backup berkala (misal kirim salinan file DB ke email/Telegram keluarga).
- **File `README-WARISAN.md`:** Harus berisi:
  1. Lokasi hosting & URL aplikasi.
  2. Akun & password server/hosting.
  3. Cara download file database dan cara membaca file CSV di Excel.
  4. Kontak 1 rekan programmer yang bisa dimintai tolong jika pembuat berhalangan tetap.
