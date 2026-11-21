import { registerSW } from 'virtual:pwa-register';

// Register Service Worker for PWA
registerSW({
    immediate: true,
    onNeedRefresh() {
        console.log('Versi aplikasi baru tersedia.');
    },
    onOfflineReady() {
        console.log('Aplikasi siap dipakai offline.');
    },
});
