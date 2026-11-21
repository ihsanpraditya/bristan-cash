# PANDUAN WARISAN DIGITAL - APLIKASI KEUANGAN KELUARGA

Dokumen ini ditulis agar aplikasi catatan keuangan keluarga ini tetap dapat diakses, dirawat, dan diteruskan oleh istri atau keluarga tercinta kapan pun dibutuhkan, bahkan jika pembuat aplikasi ini sudah tiada.

---

## 1. Informasi Aplikasi & Akses
- **Nama Aplikasi:** Keuangan Keluarga
- **Alamat / URL Aplikasi:** (Isi URL domain aplikasi Anda di sini, misal: `https://keuangan.keluarga.com`)
- **Login Admin/Keluarga (Default Seeder):**
  - Email: `keluarga@bristan.local`
  - Password: `password` *(Dapat diganti di menu Pengaturan Akun)*

---

## 2. Penyimpanan Data & Cara Backup
Aplikasi ini sengaja dibuat menggunakan teknologi **SQLite**. Seluruh data transaksi, saldo akun, dan kategori tersimpan rapi dalam **1 buah file saja**:

- **Lokasi File Database:** `database/database.sqlite`
- **Cara Backup Manual:**
  1. Akses server/hosting tempat aplikasi berada.
  2. Download/copy file `database/database.sqlite`.
  3. Simpan file tersebut di flashdisk atau Google Drive keluarga.
- **Cara Ekspor ke Excel (Paling Mudah untuk Istri):**
  1. Buka aplikasi di HP.
  2. Klik tombol ikon **Download / Ekspor CSV** di kanan atas.
  3. File `.csv` langsung terunduh dan bisa dibuka di Microsoft Excel atau Google Sheets.

---

## 3. Informasi Hosting & Server
- **Penyedia Hosting / VPS:** (Misal: DigitalOcean / Hetzner / Laravel Cloud / Rumahweb)
- **Akun Hosting:** (Email pendaftaran)
- **Metode Pembayaran Hosting:** (Kartu kredit / auto-debit yang digunakan agar hosting tidak mati)

---

## 4. Kontak Bantuan Programmer / Teman
Jika ada kendala teknis dan istri/keluarga membutuhkan bantuan programmer untuk memperbaiki atau memindahkan server:

- **Nama Teman Programmer:** (Nama sahabat programmer Anda)
- **No. WhatsApp / Telepon:** (Nomor telepon)
- **Email:** (Alamat email)
- **Catatan untuk Programmer Penerus:**
  *Stack: Laravel 13, PHP 8.3+, Livewire 4, Tailwind CSS 4, SQLite. Tanpa library aneh, standar resmi Laravel.*

---

*"Catatan ini dibuat dengan penuh cinta untuk masa depan dan ketenangan keluarga."*
