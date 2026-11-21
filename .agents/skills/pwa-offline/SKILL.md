# SKILL - PWA & Offline

## Konsep
- PWA != SPA. PWA = bisa di-install, ada icon, bisa offline. SPA = tanpa refresh.
- Aplikasi ini harus keduanya: PWA + rasa SPA via Livewire.

## PWA Setup (Laravel 13 + Vite)
- Pakai `vite-plugin-pwa` di vite.config.js
- Wajib ada: manifest.json (name: Keuangan Keluarga, display: standalone, theme_color: #16a34a), icons 192x192 & 512x512
- Wajib HTTPS untuk testing di HP. Di localhost, tombol install tidak akan muncul.
- Di Blade layout: <link rel="manifest" href="/manifest.json"> + <meta name="theme-color">

## Offline Strategy (Jarang Offline, Tapi Harus Aman)
- Laravel + SQLite itu server-side, tidak bisa nulis DB saat offline.
- Solusi: ANTRIAN OFFLINE, bukan database lokal 2 arah (terlalu rumit & rawan konflik).

Alur:
1. Online: Livewire kirim langsung ke server.
2. Offline: Alpine.js simpan JSON transaksi ke IndexedDB (pakai library idb-keyval).
3. Tampilkan label kuning: "Tersimpan offline, akan terkirim otomatis"
4. Saat event `window.addEventListener('online')`: ambil semua antrean dari IndexedDB, kirim satu-satu ke POST /api/transactions, hapus yang sukses.
5. Cache dengan Workbox: cache halaman utama + CSS/JS + data terakhir, agar saat offline user tetap bisa buka app dan lihat saldo terakhir, bukan halaman putih.
6. Tampilkan indikator: Dot merah jika ada antrean belum terkirim, dot hijau jika semua tersimpan.
