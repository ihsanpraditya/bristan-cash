{{-- Bottom Sticky Bar (Thumb Zone) --}}
<div class="fixed bottom-0 inset-x-0 z-40 bg-white/90 dark:bg-neutral-950/90 backdrop-blur-lg border-t border-neutral-200 dark:border-neutral-800 p-3 pb-safe">
    <div class="max-w-2xl mx-auto flex items-center justify-between gap-2">
        {{-- Tombol Pengeluaran (Merah - Paling Sering Digunakan) --}}
        <button type="button" wire:click="openInput('keluar')"
            class="flex-1 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white py-3.5 px-3 rounded-2xl font-bold text-sm shadow-lg shadow-rose-600/25 flex items-center justify-center gap-2 transition">
            <span class="text-base">↑</span>
            <span>Pengeluaran</span>
        </button>

        {{-- Tombol Pindah Saldo / Tarik Tunai (Biru) --}}
        <button type="button" wire:click="openInput('transfer')"
            class="bg-sky-600 hover:bg-sky-700 active:scale-95 text-white py-3.5 px-4 rounded-2xl font-bold text-sm shadow-lg shadow-sky-600/25 flex items-center justify-center gap-1.5 transition">
            <span class="text-base">⇄</span>
            <span class="hidden sm:inline">Pindah</span>
        </button>

        {{-- Tombol Pemasukan (Hijau) --}}
        <button type="button" wire:click="openInput('masuk')"
            class="flex-1 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white py-3.5 px-3 rounded-2xl font-bold text-sm shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2 transition">
            <span class="text-base">↓</span>
            <span>Pemasukan</span>
        </button>
    </div>
</div>
