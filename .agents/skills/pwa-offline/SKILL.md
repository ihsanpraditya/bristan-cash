---
name: pwa-offline
description: Setup and offline queue architecture for PWA in Laravel Livewire using vite-plugin-pwa, Alpine.js, and IndexedDB.
---

# SKILL - PWA & Offline Strategy

## Konsep Utama
- **PWA (Progressive Web App):** Bisa di-*install* ke Home Screen HP, ikon aplikasi native, tampilan *fullscreen standalone* (tanpa address bar browser), dan aset instan via cache.
- **SPA Feel:** Halaman tetap reaktif tanpa reload penuh berkat Livewire & Alpine.js.
- **Prinsip Offline:** **Antrean 1 Arah (One-Way Offline Queue)** menggunakan IndexedDB. Bukan sinkronisasi 2 arah yang rumit.

## PWA Setup (Laravel 13 + Vite)

### 1. Dependensi Wajib
- NPM: `vite-plugin-pwa`, `idb-keyval` (library ringan < 1KB untuk IndexedDB).

### 2. Konfigurasi `vite.config.js`
- Pastikan Service Worker ditempatkan di root `public/` agar mengontrol scope `/` (seluruh aplikasi):
  ```javascript
  VitePWA({
    registerType: 'autoUpdate',
    scope: '/',
    base: '/',
    manifest: {
      name: 'Keuangan Keluarga',
      short_name: 'Keuangan',
      start_url: '/',
      display: 'standalone',
      background_color: '#ffffff',
      theme_color: '#16a34a',
      icons: [
        { src: '/icons/icon-192x192.png', sizes: '192x192', type: 'image/png' },
        { src: '/icons/icon-512x512.png', sizes: '512x512', type: 'image/png' }
      ]
    },
    workbox: {
      globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
      navigateFallback: '/',
      runtimeCaching: [
        {
          urlPattern: ({ request }) => request.mode === 'navigate',
          handler: 'NetworkFirst',
          options: {
            cacheName: 'html-cache',
            networkTimeoutSeconds: 3
          }
        }
      ]
    }
  })
  ```

### 3. Tag Wajib di Blade Layout (`app.blade.php`)
```html
<!-- PWA Manifest & Colors -->
<meta name="theme-color" content="#16a34a">
<link rel="manifest" href="/manifest.webmanifest">

<!-- Khusus iOS Safari Standalone -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Keuangan">
<link rel="apple-touch-icon" href="/icons/icon-192x192.png">
```

### 4. Testing di HP
- Browser HP **wajib HTTPS** agar tombol "Install / Add to Home Screen" muncul.
- Untuk local testing dari HP via WiFi lokal: gunakan tunneling HTTPS seperti Cloudflare Tunnel (`bunx cloudflared tunnel --url http://localhost:8000`) atau ngrok.

---

## Arsitektur Antrean Offline (Offline Queue)

```
[Form Input di HP]
        │
        ├─► Jika Online  ──► Kirim langsung ke Livewire ($wire.save)
        │
        └─► Jika Offline ──► Simpan ke IndexedDB ('offline_queue')
                             ├─► Tampilkan Toast Kuning ("Tersimpan offline")
                             ├─► Kurangi Saldo di Layar (Optimistic UI via Alpine)
                             └─► Nyalakan Indikator Dot Kuning/Merah
```

### Alur Sinkronisasi Saat Online Kembali:
1. Event `window.addEventListener('online')` mendeteksi koneksi pulih.
2. Ambil seluruh data antrean dari IndexedDB menggunakan `idb-keyval.get('offline_queue')`.
3. **Kirim Sekaligus (Batch):**
   - Kirim array transaksi sekaligus ke backend (via `$wire.syncBatch(queue)` atau endpoint `POST /api/sync-transactions`).
4. Backend memproses dalam `DB::transaction()`:
   - Simpan semua transaksi yang valid.
5. Jika server merespons sukses:
   - Kosongkan antrean: `idb-keyval.set('offline_queue', [])`.
   - Ubah indikator menjadi **Dot Hijau** (Semua tersinkron).
   - Refresh data saldo aktual dari server.

---

## Indikator Status untuk Istri (Ramah & Menenangkan)
- **Dot Hijau:** "Semua data aman & tersimpan di server."
- **Dot Oranye / Kuning:** "Ada [X] catatan menunggu internet (aman di HP)."
- **Banner Halus:** Jangan gunakan pesan error teknis seperti *"Connection Timeout"* atau *"Network Failed"*. Cukup: *"Catatan disimpan di HP dulu ya, nanti otomatis dikirim saat ada sinyal."*
