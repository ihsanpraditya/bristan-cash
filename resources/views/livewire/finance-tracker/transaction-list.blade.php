{{-- Daftar Transaksi --}}
<div class="space-y-2 mb-6">
    <div class="flex items-center justify-between mb-2">
        <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Riwayat Catatan</h2>
        <span class="text-xs text-neutral-400">{{ $transactions->total() }} catatan</span>
    </div>

    @forelse ($transactions as $item)
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-3.5 shadow-sm flex items-center justify-between gap-3 hover:border-neutral-300 dark:hover:border-neutral-700 transition">
            <div class="flex items-center gap-3 min-w-0">
                {{-- Icon Tipe --}}
                <div class="size-11 shrink-0 rounded-2xl flex items-center justify-center font-bold text-lg {{ $item->type === 'masuk' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' : ($item->type === 'transfer' ? 'bg-sky-100 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300') }}">
                    @if ($item->type === 'masuk')
                        ↓
                    @elseif ($item->type === 'transfer')
                        ⇄
                    @else
                        ↑
                    @endif
                </div>

                {{-- Info Transaksi --}}
                <div class="min-w-0">
                    <div class="font-bold text-sm text-neutral-900 dark:text-neutral-100 truncate">
                        @if ($item->type === 'transfer')
                            Pindah Saldo: {{ $item->account?->nama }} → {{ $item->targetAccount?->nama }}
                        @else
                            {{ $item->category?->nama ?? 'Tanpa Kategori' }}
                        @endif
                    </div>
                    <div class="text-xs text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5 flex-wrap">
                        <span>{{ $item->tanggal->format('d M Y') }}</span>
                        <span>•</span>
                        @if ($item->type !== 'transfer')
                            <span>{{ $item->account?->nama }}</span>
                        @endif
                        @if (!empty($item->keterangan))
                            <span>•</span>
                            <span class="italic text-neutral-600 dark:text-neutral-300 truncate max-w-[150px]">"{{ $item->keterangan }}"</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Nominal & Tombol Aksi --}}
            <div class="text-right shrink-0">
                <div class="font-extrabold text-sm {{ $item->type === 'masuk' ? 'text-emerald-600 dark:text-emerald-400' : ($item->type === 'transfer' ? 'text-sky-600 dark:text-sky-400' : 'text-neutral-900 dark:text-neutral-100') }}">
                    {{ $item->type === 'masuk' ? '+' : ($item->type === 'keluar' ? '-' : '') }} Rp {{ number_format($item->nominal, 0, ',', '.') }}
                </div>
                <div class="flex items-center justify-end gap-2 mt-1">
                    <button type="button" wire:click="editTransaction({{ $item->id }})" class="text-xs text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200">
                        Edit
                    </button>
                    <span class="text-neutral-300 dark:text-neutral-700">•</span>
                    <button type="button" wire:click="confirmDelete({{ $item->id }})" class="text-xs text-neutral-400 hover:text-rose-500">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-3xl p-8 text-center">
            <span class="text-4xl block mb-2">☕</span>
            <p class="font-bold text-sm text-neutral-800 dark:text-neutral-200">Belum ada catatan di periode ini</p>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 mb-4">Yuk catat pengeluaran atau pemasukan pertamamu hari ini!</p>
            <button type="button" wire:click="openInput('keluar')"
                class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md transition">
                + Catat Pengeluaran
            </button>
        </div>
    @endforelse

    {{-- Pagination --}}
    <div class="pt-2">
        {{ $transactions->links() }}
    </div>
</div>
