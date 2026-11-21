{{-- Modal / Bottom Sheet Input Transaksi --}}
@if ($showInputModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
        x-data x-trap.noscroll="true">
        <div class="bg-white dark:bg-neutral-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 max-h-[92vh] overflow-y-auto">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800 mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-lg">
                        @if ($type === 'keluar') 🛒 @elseif($type === 'masuk') 💰 @else ⇄ @endif
                    </span>
                    <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100">
                        {{ $isEditing ? 'Ubah Catatan' : ($type === 'keluar' ? 'Catat Pengeluaran' : ($type === 'masuk' ? 'Catat Pemasukan' : 'Pindah Saldo / Tarik Tunai')) }}
                    </h3>
                </div>
                <button type="button" wire:click="$set('showInputModal', false)" class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 p-1">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            {{-- Tipe Selector (Tab) --}}
            @if (!$isEditing)
                <div class="flex bg-neutral-100 dark:bg-neutral-800 p-1 rounded-2xl mb-4 text-xs font-bold">
                    <button type="button" wire:click="setType('keluar')"
                        class="flex-1 py-2 rounded-xl transition {{ $type === 'keluar' ? 'bg-rose-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400' }}">
                        Pengeluaran
                    </button>
                    <button type="button" wire:click="setType('masuk')"
                        class="flex-1 py-2 rounded-xl transition {{ $type === 'masuk' ? 'bg-emerald-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400' }}">
                        Pemasukan
                    </button>
                    <button type="button" wire:click="setType('transfer')"
                        class="flex-1 py-2 rounded-xl transition {{ $type === 'transfer' ? 'bg-sky-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400' }}">
                        Pindah Saldo
                    </button>
                </div>
            @endif

            <form wire:submit="saveTransaction" class="space-y-4">
                {{-- Input Nominal Gede --}}
                <div x-data="{
                    formatRupiah(el) {
                        let clean = el.value.replace(/[^0-9]/g, '');
                        if (!clean) {
                            el.value = '';
                            return;
                        }
                        el.value = new Intl.NumberFormat('id-ID').format(clean);
                    }
                }">
                    <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Nominal (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-neutral-400 pointer-events-none">Rp</span>
                        <input type="tel" inputmode="numeric" wire:model="nominal"
                            x-on:input="formatRupiah($event.target)"
                            placeholder="0" autofocus
                            class="w-full pl-12 pr-4 py-3.5 text-2xl font-black rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                    @error('nominal') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror

                    {{-- Tombol Cepat Nominal (+000, +10rb, +50rb, +100rb) --}}
                    <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-1 scrollbar-none">
                        <button type="button" wire:click="quickAmount('000')"
                            class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-xs font-extrabold text-neutral-800 dark:text-neutral-200">
                            +000
                        </button>
                        <button type="button" wire:click="quickAmount(10000)"
                            class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                            +10rb
                        </button>
                        <button type="button" wire:click="quickAmount(50000)"
                            class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                            +50rb
                        </button>
                        <button type="button" wire:click="quickAmount(100000)"
                            class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                            +100rb
                        </button>
                        <button type="button" wire:click="$set('nominal', '')"
                            class="px-2.5 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 text-neutral-500 text-xs font-bold ml-auto">
                            Reset
                        </button>
                    </div>
                </div>

                {{-- Pilihan Kategori (Jika Keluar / Masuk) --}}
                @if ($type !== 'transfer')
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1.5">Kategori</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1">
                            @foreach ($categories->where('tipe', $type) as $cat)
                                <button type="button" wire:click="$set('category_id', {{ $cat->id }})"
                                    class="p-2.5 rounded-2xl border text-left text-xs font-bold flex items-center gap-2 transition {{ $category_id === $cat->id ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200 ring-2 ring-emerald-500/20' : 'border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800' }}">
                                    <span class="size-2 rounded-full {{ $category_id === $cat->id ? 'bg-emerald-500' : 'bg-neutral-300 dark:bg-neutral-600' }}"></span>
                                    <span class="truncate">{{ $cat->nama }}</span>
                                </button>
                            @endforeach
                        </div>
                        @error('category_id') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>
                @endif

                {{-- Pilihan Akun Sumber & Tujuan --}}
                <div class="grid grid-cols-1 {{ $type === 'transfer' ? 'sm:grid-cols-2' : '' }} gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            {{ $type === 'transfer' ? 'Dari Rekening / Dompet' : 'Pakai Rekening / Dompet' }}
                        </label>
                        <select wire:model="account_id"
                            class="w-full px-3.5 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->nama }} (Sisa: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                        @error('account_id') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    @if ($type === 'transfer')
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Ke Rekening / Dompet</label>
                            <select wire:model="target_account_id"
                                class="w-full px-3.5 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <option value="">-- Pilih Tujuan --</option>
                                @foreach ($accounts as $acc)
                                    @if ($acc->id !== (int) $account_id)
                                        <option value="{{ $acc->id }}">{{ $acc->nama }} (Sisa: Rp {{ number_format($acc->balance, 0, ',', '.') }})</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('target_account_id') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                {{-- Tanggal & Catatan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Tanggal</label>
                        <input type="date" wire:model="tanggal"
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        @error('tanggal') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Catatan (Boleh Kosong)</label>
                        <input type="text" wire:model="keterangan" placeholder="Contoh: Beli sayur, token listrik..."
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full py-4 rounded-2xl text-white font-extrabold text-base shadow-xl transition active:scale-[0.98] {{ $type === 'keluar' ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30' : ($type === 'masuk' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30' : 'bg-sky-600 hover:bg-sky-700 shadow-sky-600/30') }}">
                        <span wire:loading.remove>{{ $isEditing ? 'Simpan Perubahan ✨' : 'Simpan Catatan 🎉' }}</span>
                        <span wire:loading>Menyimpan... ⏳</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
