<div class="max-w-2xl mx-auto px-4 pt-4 pb-28 min-h-screen relative" x-data="offlineQueueHandler()">
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

    {{-- Saldo Utama & Ringkasan --}}
    <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-5 text-white shadow-lg mb-5">
        <div class="flex items-center justify-between text-emerald-100 text-xs font-medium mb-1">
            <span>TOTAL SALDO KELUARGA</span>
            <span class="bg-white/20 px-2 py-0.5 rounded-full uppercase tracking-wider text-[10px]">Real-time</span>
        </div>
        <div class="text-3xl font-extrabold tracking-tight mb-4">
            Rp {{ number_format($totalSaldo, 0, ',', '.') }}
        </div>

        {{-- Cashflow Summary --}}
        <div class="grid grid-cols-2 gap-3 pt-3 border-t border-white/15">
            <div class="bg-white/10 rounded-2xl p-2.5">
                <div class="text-[11px] text-emerald-100 flex items-center gap-1">
                    <span>↓ Pemasukan</span>
                </div>
                <div class="text-base font-bold text-white">
                    Rp {{ number_format($totalMasukPeriode, 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white/10 rounded-2xl p-2.5">
                <div class="text-[11px] text-emerald-100 flex items-center gap-1">
                    <span>↑ Pengeluaran</span>
                </div>
                <div class="text-base font-bold text-rose-200">
                    Rp {{ number_format($totalKeluarPeriode, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

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
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Nominal (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-neutral-400">Rp</span>
                            <input type="tel" inputmode="numeric" wire:model="nominal"
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
</div>

{{-- Alpine.js Offline Queue Component Script --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('offlineQueueHandler', () => ({
            isOnline: navigator.onLine,
            offlineQueueCount: 0,

            async init() {
                window.addEventListener('online', () => {
                    this.isOnline = true;
                    this.syncNow();
                });

                window.addEventListener('offline', () => {
                    this.isOnline = false;
                });

                await this.refreshQueueCount();
            },

            async getQueue() {
                try {
                    const raw = localStorage.getItem('bristan_offline_queue');
                    return raw ? JSON.parse(raw) : [];
                } catch (e) {
                    return [];
                }
            },

            async setQueue(items) {
                localStorage.setItem('bristan_offline_queue', JSON.stringify(items));
                this.offlineQueueCount = items.length;
            },

            async refreshQueueCount() {
                const queue = await this.getQueue();
                this.offlineQueueCount = queue.length;
            },

            async syncNow() {
                if (!navigator.onLine) return;
                const queue = await this.getQueue();
                if (queue.length === 0) return;

                try {
                    // Call Livewire syncBatch
                    const count = await @this.syncBatch(queue);
                    if (count > 0) {
                        await this.setQueue([]);
                    }
                } catch (e) {
                    console.warn('Gagal sync otomatis:', e);
                }
            }
        }));
    });
</script>
