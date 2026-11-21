{{-- Filter Periode --}}
<div class="flex items-center justify-between gap-1 bg-neutral-100 dark:bg-neutral-800 p-1 rounded-2xl mb-4 text-xs font-medium">
    <button type="button" wire:click="$set('period', 'hari_ini')"
        class="flex-1 py-1.5 rounded-xl text-center transition {{ $period === 'hari_ini' ? 'bg-white dark:bg-neutral-900 shadow-sm font-bold text-emerald-600 dark:text-emerald-400' : 'text-neutral-600 dark:text-neutral-400' }}">
        Hari Ini
    </button>
    <button type="button" wire:click="$set('period', 'minggu_ini')"
        class="flex-1 py-1.5 rounded-xl text-center transition {{ $period === 'minggu_ini' ? 'bg-white dark:bg-neutral-900 shadow-sm font-bold text-emerald-600 dark:text-emerald-400' : 'text-neutral-600 dark:text-neutral-400' }}">
        Minggu Ini
    </button>
    <button type="button" wire:click="$set('period', 'bulan_ini')"
        class="flex-1 py-1.5 rounded-xl text-center transition {{ $period === 'bulan_ini' ? 'bg-white dark:bg-neutral-900 shadow-sm font-bold text-emerald-600 dark:text-emerald-400' : 'text-neutral-600 dark:text-neutral-400' }}">
        Bulan Ini
    </button>
    <button type="button" wire:click="$set('period', 'semua')"
        class="flex-1 py-1.5 rounded-xl text-center transition {{ $period === 'semua' ? 'bg-white dark:bg-neutral-900 shadow-sm font-bold text-emerald-600 dark:text-emerald-400' : 'text-neutral-600 dark:text-neutral-400' }}">
        Semua
    </button>
</div>

{{-- Filter Kategori / Tipe Aktif --}}
@if ($filterCategoryId !== null || $filterType !== null)
    <div class="flex items-center gap-2 mb-3 text-xs">
        <span class="text-neutral-500">Filter aktif:</span>
        @if ($filterCategoryId !== null)
            <span class="inline-flex items-center gap-1 bg-neutral-200 dark:bg-neutral-700 px-2 py-0.5 rounded-full">
                Kategori: {{ $categories->firstWhere('id', $filterCategoryId)?->nama }}
                <button type="button" wire:click="$set('filterCategoryId', null)" class="text-neutral-500 hover:text-neutral-800">&times;</button>
            </span>
        @endif
        @if ($filterType !== null)
            <span class="inline-flex items-center gap-1 bg-neutral-200 dark:bg-neutral-700 px-2 py-0.5 rounded-full capitalize">
                Tipe: {{ $filterType }}
                <button type="button" wire:click="$set('filterType', null)" class="text-neutral-500 hover:text-neutral-800">&times;</button>
            </span>
        @endif
    </div>
@endif
