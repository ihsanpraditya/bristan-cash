<div class="max-w-2xl mx-auto px-4 pt-4 pb-24 min-h-screen relative">
    {{-- Toast Notification --}}
    @if (session()->has('toast'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-neutral-900/90 text-white px-5 py-3 rounded-full text-sm font-medium shadow-xl backdrop-blur flex items-center gap-2 animate-bounce">
            <span>{{ session('toast') }}</span>
            <button type="button" @click="show = false" class="text-neutral-400 hover:text-white">&times;</button>
        </div>
    @endif

    {{-- Header & Navigasi Kembali --}}
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('dashboard') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 bg-neutral-100 dark:bg-neutral-800 px-3 py-2 rounded-xl transition">
            <span>←</span>
            <span>Kembali ke Catatan</span>
        </a>

        <button type="button" wire:click="openCreate()"
            class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-extrabold px-4 py-2.5 rounded-xl shadow-md transition">
            <span class="text-sm">+</span>
            <span>Catat Pinjaman Baru</span>
        </button>
    </div>

    {{-- Judul Halaman --}}
    <div class="mb-5">
        <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-100 tracking-tight">Hutang & Pinjaman</h1>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            Catatan uang yang sedang dipinjam orang lain atau uang yang kita pinjam, agar tidak lupa dan tetap tenang.
        </p>
    </div>

    {{-- Ringkasan 2 Kartu (Dipinjam Orang vs Hutang Kita) --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        {{-- Kartu Dipinjam Orang (Piutang) --}}
        <div wire:click="$set('activeTab', 'dipinjam_orang')"
            class="cursor-pointer p-4 rounded-3xl border transition shadow-sm {{ $activeTab === 'dipinjam_orang' ? 'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-500 ring-2 ring-emerald-500/20' : 'bg-white dark:bg-neutral-900 border-neutral-200 dark:border-neutral-800 hover:border-neutral-300' }}">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">
                    🟢 Dipinjam Orang
                </span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-200/60 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-300">
                    {{ $countDipinjamBelumLunas }} aktif
                </span>
            </div>
            <div class="text-base sm:text-lg font-black text-neutral-900 dark:text-neutral-100 truncate">
                Rp {{ number_format($totalDipinjamOrang, 0, ',', '.') }}
            </div>
            <p class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-1">Uang kita yang bakal balik</p>
        </div>

        {{-- Kartu Hutang Kita --}}
        <div wire:click="$set('activeTab', 'kita_pinjam')"
            class="cursor-pointer p-4 rounded-3xl border transition shadow-sm {{ $activeTab === 'kita_pinjam' ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-500 ring-2 ring-amber-500/20' : 'bg-white dark:bg-neutral-900 border-neutral-200 dark:border-neutral-800 hover:border-neutral-300' }}">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                    🤝 Hutang Kita
                </span>
                <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-200/60 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300">
                    {{ $countHutangBelumLunas }} aktif
                </span>
            </div>
            <div class="text-base sm:text-lg font-black text-neutral-900 dark:text-neutral-100 truncate">
                Rp {{ number_format($totalKitaPinjam, 0, ',', '.') }}
            </div>
            <p class="text-[10px] text-neutral-500 dark:text-neutral-400 mt-1">Uang yang perlu kita balikin</p>
        </div>
    </div>

    {{-- Filter Bar (Status Lunas & Pencarian) --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-5">
        {{-- Status Filter Pills --}}
        <div class="flex bg-neutral-100 dark:bg-neutral-800/80 p-1 rounded-2xl text-xs font-bold">
            <button type="button" wire:click="$set('statusFilter', 'belum_lunas')"
                class="flex-1 sm:flex-none px-4 py-2 rounded-xl transition {{ $statusFilter === 'belum_lunas' ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900' }}">
                Belum Lunas
            </button>
            <button type="button" wire:click="$set('statusFilter', 'lunas')"
                class="flex-1 sm:flex-none px-4 py-2 rounded-xl transition {{ $statusFilter === 'lunas' ? 'bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100 shadow-sm' : 'text-neutral-500 dark:text-neutral-400 hover:text-neutral-900' }}">
                Riwayat Sudah Lunas
            </button>
        </div>

        {{-- Search Input --}}
        <div class="relative flex-1 sm:max-w-xs">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama orang..."
                class="w-full pl-8 pr-3 py-2 text-xs font-semibold rounded-xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-neutral-400 text-xs">🔍</span>
        </div>
    </div>

    {{-- Daftar Kartu Hutang / Pinjaman --}}
    <div class="space-y-3">
        @forelse ($debts as $item)
            @php
                $persenBayar = $item->nominal_total > 0
                    ? min(100, round(($item->total_dibayar / $item->nominal_total) * 100))
                    : 0;
            @endphp
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-4 sm:p-5 shadow-sm hover:border-neutral-300 dark:hover:border-neutral-700 transition">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-black text-base text-neutral-900 dark:text-neutral-100">
                                {{ $item->nama_orang }}
                            </h3>
                            @if ($item->status === 'lunas')
                                <span class="bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full">
                                    ✓ Lunas
                                </span>
                            @elseif ($item->jatuh_tempo && $item->jatuh_tempo->isPast())
                                <span class="bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                                    Lewat Tempo
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                            Tanggal: {{ $item->tanggal->format('d M Y') }}
                            @if ($item->jatuh_tempo)
                                &bull; <span class="{{ $item->jatuh_tempo->isPast() ? 'text-rose-500 font-bold' : '' }}">Tempo: {{ $item->jatuh_tempo->format('d M Y') }}</span>
                            @endif
                        </p>
                    </div>

                    {{-- Actions Dropdown / Tombol Hapus --}}
                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="openHistory({{ $item->id }})" title="Riwayat Cicilan"
                            class="p-2 rounded-xl text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                            <span class="text-xs font-bold">📜 Riwayat</span>
                        </button>
                        <button type="button" wire:click="confirmDelete({{ $item->id }})" title="Hapus Catatan"
                            class="p-2 rounded-xl text-neutral-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition">
                            <span class="text-xs">🗑️</span>
                        </button>
                    </div>
                </div>

                {{-- Keterangan Opsional --}}
                @if ($item->keterangan)
                    <p class="text-xs text-neutral-600 dark:text-neutral-300 bg-neutral-50 dark:bg-neutral-800/50 p-2.5 rounded-2xl mb-3">
                        💬 {{ $item->keterangan }}
                    </p>
                @endif

                {{-- Status Nominal & Progress Cicilan --}}
                <div class="bg-neutral-50 dark:bg-neutral-800/60 p-3 rounded-2xl mb-3">
                    <div class="flex items-center justify-between text-xs mb-1.5">
                        <span class="text-neutral-500 dark:text-neutral-400 font-medium">Sisa yang belum selesai:</span>
                        <span class="font-black text-sm {{ $item->type === 'dipinjam_orang' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            Rp {{ number_format($item->sisa_nominal, 0, ',', '.') }}
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 h-2 rounded-full overflow-hidden mb-1.5">
                        <div class="h-full rounded-full transition-all duration-500 {{ $item->type === 'dipinjam_orang' ? 'bg-emerald-500' : 'bg-amber-500' }}"
                            style="width: {{ $persenBayar }}%"></div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-neutral-400">
                        <span>Sudah bayar: Rp {{ number_format($item->total_dibayar, 0, ',', '.') }} ({{ $persenBayar }}%)</span>
                        <span>Total: Rp {{ number_format($item->nominal_total, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Tombol Aksi Pelunasan / Uang Balik --}}
                @if ($item->status === 'belum_lunas')
                    <div class="flex items-center gap-2 pt-1">
                        <button type="button" wire:click="openPayment({{ $item->id }})"
                            class="flex-1 py-3 px-4 rounded-2xl font-extrabold text-xs text-white shadow-md transition active:scale-[0.98] flex items-center justify-center gap-2 {{ $item->type === 'dipinjam_orang' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20' }}">
                            <span>💸</span>
                            <span>{{ $item->type === 'dipinjam_orang' ? 'Catat Uang Balik (Cicil / Lunas)' : 'Bayar Cicilan / Lunas' }}</span>
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-neutral-900 rounded-3xl border border-neutral-200 dark:border-neutral-800 p-6">
                <span class="text-4xl block mb-2">🎉</span>
                <h3 class="font-bold text-sm text-neutral-800 dark:text-neutral-200">
                    {{ $statusFilter === 'belum_lunas' ? 'Tidak ada catatan pinjaman yang aktif!' : 'Belum ada riwayat pinjaman lunas.' }}
                </h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                    {{ $statusFilter === 'belum_lunas' ? 'Alhamdulillah, keuangan keluarga bebas dari tanggungan pinjaman.' : 'Semua catatan yang sudah lunas akan tampil di sini.' }}
                </p>
                @if ($statusFilter === 'belum_lunas')
                    <button type="button" wire:click="openCreate()"
                        class="mt-4 inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow transition">
                        <span>+ Catat Pinjaman Baru</span>
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    {{-- MODAL 1: Tambah / Edit Catatan Pinjaman --}}
    @if ($showFormModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            x-data x-trap.noscroll="true">
            <div class="bg-white dark:bg-neutral-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800 mb-4">
                    <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
                        <span>{{ $type === 'dipinjam_orang' ? '🟢' : '🤝' }}</span>
                        <span>Catat Pinjaman Baru</span>
                    </h3>
                    <button type="button" wire:click="$set('showFormModal', false)" class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 p-1">
                        ✕
                    </button>
                </div>

                {{-- Tipe Selector Tab --}}
                <div class="flex bg-neutral-100 dark:bg-neutral-800 p-1 rounded-2xl mb-4 text-xs font-bold">
                    <button type="button" wire:click="$set('type', 'dipinjam_orang')"
                        class="flex-1 py-2.5 rounded-xl transition {{ $type === 'dipinjam_orang' ? 'bg-emerald-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400' }}">
                        🟢 Dipinjam Orang (Uang Balik Nanti)
                    </button>
                    <button type="button" wire:click="$set('type', 'kita_pinjam')"
                        class="flex-1 py-2.5 rounded-xl transition {{ $type === 'kita_pinjam' ? 'bg-amber-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400' }}">
                        🤝 Hutang Kita (Perlu Dibalikkan)
                    </button>
                </div>

                <form wire:submit="saveDebt" class="space-y-4">
                    {{-- Nama Orang --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            {{ $type === 'dipinjam_orang' ? 'Nama Orang yang Pinjam' : 'Nama Orang / Pihak yang Meminjami' }}
                        </label>
                        <input type="text" wire:model="nama_orang" placeholder="Contoh: Mba Dini, Mas Anto..."
                            class="w-full px-4 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        @error('nama_orang') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Nominal --}}
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
                                x-on:input="formatRupiah($event.target)" placeholder="0"
                                class="w-full pl-12 pr-4 py-3 text-xl font-black rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        @error('nominal') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror

                        {{-- Tombol Cepat Nominal --}}
                        <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-1 scrollbar-none">
                            <button type="button" wire:click="quickAmount('000')"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 text-xs font-extrabold text-neutral-800 dark:text-neutral-200">
                                +000
                            </button>
                            <button type="button" wire:click="quickAmount(50000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +50rb
                            </button>
                            <button type="button" wire:click="quickAmount(100000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +100rb
                            </button>
                            <button type="button" wire:click="quickAmount(500000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +500rb
                            </button>
                            <button type="button" wire:click="$set('nominal', '')"
                                class="px-2.5 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 text-neutral-500 text-xs font-bold ml-auto">
                                Reset
                            </button>
                        </div>
                    </div>

                    {{-- Pilihan Rekening / Dompet Kas --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            {{ $type === 'dipinjam_orang' ? 'Uang Diambil Dari Dompet Mana?' : 'Uang Masuk ke Dompet Mana?' }}
                        </label>
                        <select wire:model="account_id"
                            class="w-full px-3.5 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- Catatan Saja (Tanpa Mengubah Saldo Dompet) --</option>
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->nama }} (Saldo: Rp {{ number_format($acc->balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-neutral-400 mt-1">
                            {{ $type === 'dipinjam_orang' ? 'Jika dipilih, saldo rekening langsung dipotong otomatis.' : 'Jika dipilih, saldo rekening langsung bertambah otomatis.' }}
                        </p>
                    </div>

                    {{-- Tanggal & Jatuh Tempo --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Tanggal</label>
                            <input type="date" wire:model="tanggal"
                                class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Jatuh Tempo (Opsional)</label>
                            <input type="date" wire:model="jatuh_tempo"
                                class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                    </div>

                    {{-- Keterangan --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Catatan Tambahan (Opsional)</label>
                        <input type="text" wire:model="keterangan" placeholder="Contoh: Buat beli pulsa darurat..."
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    {{-- Submit --}}
                    <div class="pt-3">
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-4 rounded-2xl text-white font-extrabold text-base shadow-xl transition active:scale-[0.98] {{ $type === 'dipinjam_orang' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30' : 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/30' }}">
                            <span wire:loading.remove>Simpan Catatan 🎉</span>
                            <span wire:loading>Menyimpan... ⏳</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 2: Catat Pembayaran / Pelunasan / Uang Balik --}}
    @if ($showPaymentModal && $paymentDebt)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            x-data x-trap.noscroll="true">
            <div class="bg-white dark:bg-neutral-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100">
                            {{ $paymentDebt->type === 'dipinjam_orang' ? 'Catat Uang Balik 💰' : 'Bayar Hutang / Cicilan 🤝' }}
                        </h3>
                        <p class="text-xs text-neutral-500 mt-0.5">
                            Pihak: <span class="font-bold text-neutral-700 dark:text-neutral-300">{{ $paymentDebt->nama_orang }}</span>
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 p-1">
                        ✕
                    </button>
                </div>

                {{-- Banner Sisa Hutang --}}
                <div class="p-3.5 rounded-2xl mb-4 flex items-center justify-between {{ $paymentDebt->type === 'dipinjam_orang' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-100' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-900 dark:text-amber-100' }}">
                    <span class="text-xs font-semibold">Sisa yang belum selesai:</span>
                    <span class="text-base font-black">
                        Rp {{ number_format($paymentDebt->sisa_nominal, 0, ',', '.') }}
                    </span>
                </div>

                <form wire:submit="savePayment" class="space-y-4">
                    {{-- Input Nominal Bayar --}}
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
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500">Nominal yang Dibayar (Rp)</label>
                            <button type="button" wire:click="$set('paymentNominal', '{{ number_format($paymentDebt->sisa_nominal, 0, ',', '.') }}')"
                                class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 hover:underline">
                                Bayar Semua (Lunas)
                            </button>
                        </div>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-neutral-400 pointer-events-none">Rp</span>
                            <input type="tel" inputmode="numeric" wire:model="paymentNominal"
                                x-on:input="formatRupiah($event.target)" placeholder="0" autofocus
                                class="w-full pl-12 pr-4 py-3 text-xl font-black rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        @error('paymentNominal') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror

                        {{-- Quick amount chips --}}
                        <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-1 scrollbar-none">
                            <button type="button" wire:click="quickPaymentAmount('000')"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 text-xs font-extrabold text-neutral-800 dark:text-neutral-200">
                                +000
                            </button>
                            <button type="button" wire:click="quickPaymentAmount(50000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +50rb
                            </button>
                            <button type="button" wire:click="quickPaymentAmount(100000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +100rb
                            </button>
                        </div>
                    </div>

                    {{-- Pilih Rekening Kas Masuk / Keluar --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            {{ $paymentDebt->type === 'dipinjam_orang' ? 'Uang Masuk ke Dompet Mana?' : 'Dibayar Pakai Dompet Mana?' }}
                        </label>
                        <select wire:model="paymentAccountId"
                            class="w-full px-3.5 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            @foreach ($accounts as $acc)
                                <option value="{{ $acc->id }}">
                                    {{ $acc->nama }} (Saldo: Rp {{ number_format($acc->balance, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('paymentAccountId') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tanggal & Catatan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Tanggal Bayar</label>
                            <input type="date" wire:model="paymentTanggal"
                                class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">Catatan Cicilan (Opsional)</label>
                            <input type="text" wire:model="paymentCatatan" placeholder="Contoh: Cicilan ke-1..."
                                class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-3">
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-4 rounded-2xl text-white font-extrabold text-base shadow-xl transition active:scale-[0.98] {{ $paymentDebt->type === 'dipinjam_orang' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30' : 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/30' }}">
                            <span wire:loading.remove>Simpan Pembayaran ✨</span>
                            <span wire:loading>Menyimpan... ⏳</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 3: Riwayat Pembayaran / Cicilan --}}
    @if ($showHistoryModal && $targetDebt)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            x-data x-trap.noscroll="true">
            <div class="bg-white dark:bg-neutral-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 max-h-[92vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100">
                            Riwayat Pembayaran & Cicilan
                        </h3>
                        <p class="text-xs text-neutral-500">
                            {{ $targetDebt->nama_orang }} &bull; Total: Rp {{ number_format($targetDebt->nominal_total, 0, ',', '.') }}
                        </p>
                    </div>
                    <button type="button" wire:click="$set('showHistoryModal', false)" class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 p-1">
                        ✕
                    </button>
                </div>

                {{-- Status Sisa --}}
                <div class="p-3 bg-neutral-50 dark:bg-neutral-800/60 rounded-2xl mb-4 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-neutral-400 block text-[10px] uppercase font-bold">Total Terbayar</span>
                        <span class="font-extrabold text-neutral-800 dark:text-neutral-200">Rp {{ number_format($targetDebt->total_dibayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-neutral-400 block text-[10px] uppercase font-bold">Sisa</span>
                        <span class="font-extrabold {{ $targetDebt->sisa_nominal > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $targetDebt->sisa_nominal > 0 ? 'Rp '.number_format($targetDebt->sisa_nominal, 0, ',', '.') : '✓ LUNAS' }}
                        </span>
                    </div>
                </div>

                {{-- List Cicilan --}}
                <div class="space-y-2 mb-4">
                    <span class="text-[11px] uppercase font-bold text-neutral-400 tracking-wider">Daftar Pembayaran</span>
                    @forelse ($targetDebt->payments->sortByDesc('tanggal') as $pay)
                        <div class="p-3 bg-white dark:bg-neutral-800/40 border border-neutral-200 dark:border-neutral-700/60 rounded-2xl flex items-center justify-between gap-3">
                            <div>
                                <span class="font-black text-sm text-neutral-900 dark:text-neutral-100 block">
                                    Rp {{ number_format($pay->nominal, 0, ',', '.') }}
                                </span>
                                <span class="text-[11px] text-neutral-400">
                                    {{ $pay->tanggal->format('d M Y') }} &bull; Dompet: {{ $pay->account?->nama ?? '-' }}
                                    @if ($pay->catatan) &bull; <i>{{ $pay->catatan }}</i> @endif
                                </span>
                            </div>
                            <button type="button" wire:click="deletePayment({{ $pay->id }})"
                                class="text-xs text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 p-2 rounded-xl transition" title="Hapus cicilan ini">
                                🗑️
                            </button>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-400 text-center py-4">Belum ada catatan cicilan / pembayaran.</p>
                    @endforelse
                </div>

                <button type="button" wire:click="$set('showHistoryModal', false)"
                    class="w-full py-3 rounded-2xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-bold text-xs">
                    Tutup
                </button>
            </div>
        </div>
    @endif

    {{-- MODAL 4: Konfirmasi Hapus Debt --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
            x-data x-trap.noscroll="true">
            <div class="bg-white dark:bg-neutral-900 w-full max-w-sm rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 text-center">
                <span class="text-4xl block mb-2">⚠️</span>
                <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100 mb-2">Hapus Catatan Ini?</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-5 leading-relaxed">
                    Menghapus catatan ini juga akan membatalkan seluruh mutasi saldo rekening yang terkait dengan hutang ini agar saldo tetap seimbang.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="flex-1 py-3 rounded-2xl bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 font-bold text-xs">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteDebt"
                        class="flex-1 py-3 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/30">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
