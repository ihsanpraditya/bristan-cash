{{-- Modal Konfirmasi Hapus --}}
@if ($showDeleteModal)
    <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" x-data x-trap.noscroll="true">
        <div class="bg-white dark:bg-neutral-900 w-full max-w-sm rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 text-center">
            <span class="text-4xl block mb-2">🗑️</span>
            <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100 mb-1">Hapus Catatan Ini?</h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-5">Saldo akan otomatis dikembalikan seperti sebelum transaksi dicatat.</p>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="$set('showDeleteModal', false)"
                    class="flex-1 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 font-bold text-xs">
                    Batal
                </button>
                <button type="button" wire:click="deleteTransaction"
                    class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">
                    Hapus
                </button>
            </div>
        </div>
    </div>
@endif
