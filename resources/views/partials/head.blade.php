<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/icons/icon-192x192.png">

<!-- PWA Manifest & Colors -->
<meta name="theme-color" content="#16a34a">
<link rel="manifest" href="/manifest.webmanifest">

<!-- iOS Safari Standalone PWA Support -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Keuangan">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
