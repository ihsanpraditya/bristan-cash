{{-- Toast Notification --}}
@if (session()->has('toast'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-neutral-900/90 text-white px-5 py-3 rounded-full text-sm font-medium shadow-xl backdrop-blur flex items-center gap-2 animate-bounce">
        <span>{{ session('toast') }}</span>
        <button type="button" @click="show = false" class="text-neutral-400 hover:text-white">&times;</button>
    </div>
@endif

{{-- Offline Toast Alert --}}
<div x-show="offlineQueueCount > 0" x-cloak
    class="mb-3 bg-amber-500/15 border border-amber-500/30 text-amber-900 dark:text-amber-200 px-4 py-2.5 rounded-2xl text-xs font-medium flex items-center justify-between">
    <div class="flex items-center gap-2">
        <span class="size-2 rounded-full bg-amber-500 animate-ping"></span>
        <span>Ada <b x-text="offlineQueueCount"></b> catatan tersimpan di HP. Otomatis dikirim saat sinyal pulih.</span>
    </div>
    <button type="button" @click="syncNow()" class="underline font-semibold ml-2">Kirim Sekarang</button>
</div>

{{-- App Header --}}
<div class="flex items-center justify-between mb-4">
    <div>
        <div class="flex items-center gap-2">
            <span class="text-2xl">🌱</span>
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">Keuangan Keluarga</h1>
        </div>
        <p class="text-xs text-neutral-500 dark:text-neutral-400">Catatan simpel harian anti ribet</p>
    </div>

    <div class="flex items-center gap-2">
        {{-- Online / Offline Badge --}}
        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border"
            :class="isOnline ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800'">
            <span class="size-2 rounded-full" :class="isOnline ? 'bg-emerald-500' : 'bg-amber-500'"></span>
            <span x-text="isOnline ? 'Online' : 'Offline'"></span>
        </div>

        {{-- Tombol Cepat Anggaran --}}
        <a href="{{ route('budgets.index') }}" wire:navigate
            title="Kelola Anggaran Belanja"
            class="flex items-center gap-1 px-2.5 py-2 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-300 text-xs font-bold transition">
            <span>🎯</span>
            <span class="hidden sm:inline">Anggaran</span>
        </a>

        {{-- Tombol Cepat Hutang & Pinjaman --}}
        <a href="{{ route('debts.index') }}" wire:navigate
            title="Catatan Hutang & Pinjaman"
            class="flex items-center gap-1 px-2.5 py-2 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-300 text-xs font-bold transition">
            <span>🤝</span>
            <span class="hidden sm:inline">Pinjaman</span>
        </a>

        {{-- Export CSV 1-Klik --}}
        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled"
            title="Unduh catatan ke Excel / CSV"
            class="p-2 rounded-xl border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-300 transition">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
        </button>
    </div>
</div>
