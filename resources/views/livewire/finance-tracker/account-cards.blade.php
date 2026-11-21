{{-- Kartu Rekening / Dompet (Scroll Horizontal) --}}
<div class="mb-5">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Dompet & Rekening</h2>
        @if ($filterAccountId !== null)
            <button type="button" wire:click="$set('filterAccountId', null)" class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                Reset Filter
            </button>
        @endif
    </div>
    <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-none snap-x">
        @foreach ($accounts as $acc)
            <button type="button" wire:click="$set('filterAccountId', {{ $filterAccountId === $acc->id ? 'null' : $acc->id }})"
                class="snap-start shrink-0 min-w-[130px] p-3.5 rounded-2xl border text-left transition relative overflow-hidden {{ $filterAccountId === $acc->id ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-950/20' : 'border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 shadow-sm' }}">
                <div class="flex items-center gap-2 mb-2">
                    <span class="size-3 rounded-full" style="background-color: {{ $acc->warna ?? '#10b981' }}"></span>
                    <span class="text-xs font-bold text-neutral-900 dark:text-neutral-100 truncate">{{ $acc->nama }}</span>
                </div>
                <div class="text-sm font-extrabold text-neutral-900 dark:text-neutral-100">
                    Rp {{ number_format($acc->balance, 0, ',', '.') }}
                </div>
                @if ($filterAccountId === $acc->id)
                    <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium mt-1">✓ Difilter</div>
                @endif
            </button>
        @endforeach
    </div>
</div>
