<div class="max-w-2xl mx-auto px-4 pt-4 pb-20 min-h-screen relative">
        {{-- Toast Notification --}}
        @if (session()->has('toast'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-neutral-900/90 text-white px-5 py-3 rounded-full text-sm font-medium shadow-xl backdrop-blur flex items-center gap-2 animate-bounce">
                <span>{{ session('toast') }}</span>
                <button type="button" @click="show = false" class="text-neutral-400 hover:text-white">&times;</button>
            </div>
        @endif

        {{-- Header & Navigasi Kembali --}}
        <div class="flex items-center justify-between mb-5">
            <a href="{{ route('dashboard') }}" wire:navigate
                class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 bg-neutral-100 dark:bg-neutral-800 px-3 py-2 rounded-xl transition">
                <span>←</span>
                <span>Kembali ke Catatan</span>
            </a>

            <button type="button" wire:click="openCreate"
                class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-md transition">
                <span>+</span>
                <span>Tambah Dompet Baru</span>
            </button>
        </div>

        {{-- Judul Halaman --}}
        <div class="mb-5">
            <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-100 tracking-tight">Dompet & Rekening Keluarga</h1>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                Atur kantong uang keluarga: rekening bank (BCA, Mandiri), e-wallet (GoPay, ShopeePay), atau dompet uang tunai.
            </p>
        </div>

        {{-- Ringkasan Total Saldo --}}
        <div class="bg-neutral-900 text-white dark:bg-neutral-800/80 rounded-2xl p-4 mb-5 flex items-center justify-between">
            <div>
                <span class="text-[11px] uppercase tracking-wider text-neutral-400 font-semibold block">Total Saldo Seluruh Akun</span>
                <span class="text-xl font-extrabold text-emerald-400">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</span>
            </div>
            <span class="text-xs font-medium bg-neutral-800 dark:bg-neutral-700 text-neutral-300 px-3 py-1.5 rounded-xl">
                {{ $accounts->count() }} Dompet Terdaftar
            </span>
        </div>

        {{-- Daftar Rekening & Dompet --}}
        <div class="space-y-3">
            @forelse ($accounts as $item)
                @php
                    $trxCount = $item->transactions_count + $item->incoming_transfers_count;
                @endphp
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-4 shadow-sm flex items-center justify-between gap-3 hover:border-neutral-300 dark:hover:border-neutral-700 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        {{-- Tag Warna & Icon --}}
                        <div class="size-12 shrink-0 rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-sm"
                            style="background-color: {{ $item->warna ?? '#16a34a' }}">
                            @if ($item->icon === 'credit-card')
                                💳
                            @elseif ($item->icon === 'device-phone-mobile')
                                📱
                            @elseif ($item->icon === 'banknotes')
                                💵
                            @elseif ($item->icon === 'building-library')
                                🏦
                            @else
                                👛
                            @endif
                        </div>

                        {{-- Detail Akun --}}
                        <div class="min-w-0">
                            <div class="font-bold text-sm text-neutral-900 dark:text-neutral-100 flex items-center gap-2">
                                <span class="truncate">{{ $item->nama }}</span>
                            </div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 flex items-center gap-2 flex-wrap">
                                <span>Saldo Awal: Rp {{ number_format($item->saldo_awal, 0, ',', '.') }}</span>
                                <span>•</span>
                                <span class="{{ $trxCount > 0 ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-neutral-400' }}">
                                    {{ $trxCount }} transaksi
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Saldo Saat Ini & Aksi --}}
                    <div class="text-right shrink-0">
                        <div class="font-extrabold text-sm text-neutral-900 dark:text-neutral-100">
                            Rp {{ number_format($item->balance, 0, ',', '.') }}
                        </div>
                        <div class="flex items-center justify-end gap-2 mt-1.5">
                            <button type="button" wire:click="edit({{ $item->id }})"
                                class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100 bg-neutral-100 dark:bg-neutral-800 px-2.5 py-1 rounded-lg transition">
                                Ubah
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $item->id }})"
                                class="text-xs font-semibold text-rose-500 hover:text-rose-700 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1 rounded-lg transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-3xl p-8 text-center">
                    <span class="text-4xl block mb-2">👛</span>
                    <p class="font-bold text-sm text-neutral-800 dark:text-neutral-200">Belum ada dompet atau rekening</p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 mb-4">Buat rekening atau dompet pertamamu untuk mulai mencatat keuangan.</p>
                    <button type="button" wire:click="openCreate"
                        class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md">
                        + Tambah Dompet Baru
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Modal Tambah / Ubah Akun --}}
        @if ($showModal)
            <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
                x-data x-trap.noscroll="true">
                <div class="bg-white dark:bg-neutral-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 max-h-[92vh] overflow-y-auto">
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">👛</span>
                            <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100">
                                {{ $isEditing ? 'Ubah Dompet / Rekening' : 'Tambah Dompet / Rekening Baru' }}
                            </h3>
                        </div>
                        <button type="button" wire:click="$set('showModal', false)" class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 p-1">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>

                    <form wire:submit="save" class="space-y-4">
                        {{-- Nama Dompet --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                                Nama Dompet / Rekening
                            </label>
                            <input type="text" wire:model="nama" placeholder="Contoh: BCA, GoPay, Dompet Tunai, Mandiri..." autofocus
                                class="w-full px-3.5 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            @error('nama') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Saldo Awal --}}
                        <div x-data="{
                            formatRupiah(el) {
                                let clean = el.value.replace(/[^0-9]/g, '');
                                if (!clean) {
                                    el.value = '0';
                                    return;
                                }
                                el.value = new Intl.NumberFormat('id-ID').format(clean);
                            }
                        }">
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                                Saldo Awal (Rp)
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-neutral-400 pointer-events-none">Rp</span>
                                <input type="tel" inputmode="numeric" wire:model="saldo_awal"
                                    x-on:input="formatRupiah($event.target)"
                                    placeholder="0"
                                    class="w-full pl-12 pr-4 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-lg font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            </div>
                            <p class="text-[11px] text-neutral-400 mt-1">Saldo saat pertama kali mulai dicatat di aplikasi ini.</p>
                            @error('saldo_awal') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>

                        {{-- Pilihan Warna Tag --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1.5">
                                Warna Label
                            </label>
                            <div class="grid grid-cols-4 gap-2">
                                @foreach ($presetColors as $hex => $label)
                                    <button type="button" wire:click="$set('warna', '{{ $hex }}')"
                                        class="p-2 rounded-2xl border flex items-center gap-2 text-xs font-semibold transition {{ $warna === $hex ? 'border-neutral-900 dark:border-white ring-2 ring-neutral-900/20 dark:ring-white/20 bg-neutral-100 dark:bg-neutral-800' : 'border-neutral-200 dark:border-neutral-700' }}">
                                        <span class="size-4 rounded-full shrink-0 shadow-sm" style="background-color: {{ $hex }}"></span>
                                        <span class="truncate text-[11px]">{{ explode(' ', $label)[0] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Pilihan Ikon --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1.5">
                                Jenis Ikon
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach ($presetIcons as $iconKey => $iconLabel)
                                    <button type="button" wire:click="$set('icon', '{{ $iconKey }}')"
                                        class="p-2.5 rounded-2xl border flex items-center gap-2 text-xs font-semibold transition {{ $icon === $iconKey ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200 ring-2 ring-emerald-500/20' : 'border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300' }}">
                                        <span class="text-base">
                                            @if ($iconKey === 'credit-card')
                                                💳
                                            @elseif ($iconKey === 'device-phone-mobile')
                                                📱
                                            @elseif ($iconKey === 'banknotes')
                                                💵
                                            @elseif ($iconKey === 'building-library')
                                                🏦
                                            @else
                                                👛
                                            @endif
                                        </span>
                                        <span class="truncate text-xs">{{ $iconLabel }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Tombol Simpan --}}
                        <div class="pt-3">
                            <button type="submit" wire:loading.attr="disabled"
                                class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-xl transition active:scale-[0.98]">
                                <span wire:loading.remove>{{ $isEditing ? 'Simpan Perubahan Dompet ✨' : 'Simpan Dompet Baru 🎉' }}</span>
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
                    <span class="text-4xl block mb-2">
                        {{ $deleteErrorMessage ? '⚠️' : '🗑️' }}
                    </span>
                    <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100 mb-1">
                        {{ $deleteErrorMessage ? 'Tidak Bisa Dihapus' : 'Hapus Dompet Ini?' }}
                    </h3>

                    @if ($deleteErrorMessage)
                        <p class="text-xs text-neutral-600 dark:text-neutral-300 mb-5 leading-relaxed">
                            {{ $deleteErrorMessage }}
                        </p>
                        <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="w-full py-2.5 rounded-xl bg-neutral-900 hover:bg-neutral-800 text-white font-bold text-xs shadow-md">
                            Mengerti
                        </button>
                    @else
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-5">
                            Dompet ini akan dihapus dari daftar. Pastikan tidak ada uang tersisa di dalamnya.
                        </p>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="$set('showDeleteModal', false)"
                                class="flex-1 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 font-bold text-xs">
                                Batal
                            </button>
                            <button type="button" wire:click="deleteAccount"
                                class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">
                                Hapus
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
