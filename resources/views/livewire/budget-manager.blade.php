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
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('dashboard') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 bg-neutral-100 dark:bg-neutral-800 px-3 py-2 rounded-xl transition">
            <span>←</span>
            <span>Kembali ke Catatan</span>
        </a>

        <button type="button" wire:click="openCreate()"
            class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-md transition">
            <span>+</span>
            <span>Atur Anggaran Baru</span>
        </button>
    </div>

    {{-- Judul Halaman --}}
    <div class="mb-4">
        <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-100 tracking-tight">Anggaran Belanja Bulanan</h1>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            Pantau batas belanja keluarga per pos kategori agar keuangan tetap sehat dan terencana.
        </p>
    </div>

    {{-- Month Navigator Bar --}}
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-2.5 shadow-sm mb-5 flex items-center justify-between">
        <button type="button" wire:click="previousMonth"
            class="p-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-300 font-bold transition">
            ←
        </button>

        <div class="text-center">
            <div class="font-extrabold text-sm text-neutral-900 dark:text-neutral-100 flex items-center justify-center gap-2">
                <span>📅 {{ $monthName }}</span>
                @if ($isCurrentMonth)
                    <span class="bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold px-2 py-0.5 rounded-full">Bulan Ini</span>
                @endif
            </div>
            @if (!$isCurrentMonth)
                <button type="button" wire:click="setThisMonth" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                    Kembali ke Bulan Ini
                </button>
            @endif
        </div>

        <button type="button" wire:click="nextMonth"
            class="p-2 rounded-xl hover:bg-neutral-100 dark:hover:bg-neutral-800 text-neutral-600 dark:text-neutral-300 font-bold transition">
            →
        </button>
    </div>

    {{-- Kartu Ringkasan Anggaran Keseluruhan --}}
    <div class="bg-gradient-to-br from-neutral-900 to-neutral-800 dark:from-neutral-900 dark:to-neutral-950 text-white rounded-3xl p-5 shadow-xl mb-6">
        <div class="flex items-center justify-between text-neutral-300 text-xs font-semibold mb-1">
            <span>TOTAL ANGGARAN BELANJA</span>
            <span>{{ $overallPercent }}% Terpakai</span>
        </div>

        <div class="flex items-baseline justify-between gap-2 mb-3">
            <div class="text-3xl font-black text-white tracking-tight">
                Rp {{ number_format($totalAnggaran, 0, ',', '.') }}
            </div>
            <div class="text-right">
                <span class="text-[11px] text-neutral-400 block">Sisa Anggaran</span>
                <span class="text-sm font-extrabold {{ $sisaAnggaran >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                    {{ $sisaAnggaran >= 0 ? 'Rp ' . number_format($sisaAnggaran, 0, ',', '.') : '- Rp ' . number_format(abs($sisaAnggaran), 0, ',', '.') }}
                </span>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-neutral-700/60 rounded-full h-3 overflow-hidden mb-3">
            <div class="h-full rounded-full transition-all duration-500 {{ $overallPercent > 100 ? 'bg-rose-500' : ($overallPercent >= 80 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                style="width: {{ min($overallPercent, 100) }}%"></div>
        </div>

        {{-- Info Belanja Terpakai --}}
        <div class="flex items-center justify-between text-xs text-neutral-300 pt-2 border-t border-neutral-700/50">
            <span>Belanja Terpakai: <b class="text-white">Rp {{ number_format($totalTerpakai, 0, ',', '.') }}</b></span>
            <span>
                @if ($totalAnggaran === 0)
                    <span class="text-neutral-400 italic">Belum ada anggaran</span>
                @elseif ($overallPercent > 100)
                    <span class="text-rose-400 font-medium">Melebihi batas 🌸</span>
                @elseif ($overallPercent >= 80)
                    <span class="text-amber-300 font-medium">Mendekati batas ⚠️</span>
                @else
                    <span class="text-emerald-400 font-medium">Masih aman 💚</span>
                @endif
            </span>
        </div>
    </div>

    {{-- Daftar Anggaran per Kategori --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-xs font-bold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">Rincian per Pos Belanja</h2>
            <span class="text-xs text-neutral-400">{{ count($budgetItems) }} Kategori</span>
        </div>

        @foreach ($budgetItems as $item)
            @php
                $cat = $item['category'];
                $budget = $item['budget'];
                $limit = $item['limit'];
                $spent = $item['spent'];
                $percent = $item['percent'];
                $remaining = $item['remaining'];
            @endphp
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-4 shadow-sm hover:border-neutral-300 dark:hover:border-neutral-700 transition">
                <div class="flex items-center justify-between gap-3 mb-2.5">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="size-11 shrink-0 rounded-2xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center text-xl">
                            @if ($cat->icon === 'shopping-cart') 🛒
                            @elseif ($cat->icon === 'sparkles') 🍜
                            @elseif ($cat->icon === 'academic-cap') 🎓
                            @elseif ($cat->icon === 'bolt') ⚡
                            @elseif ($cat->icon === 'truck') 🚗
                            @elseif ($cat->icon === 'home') 🏠
                            @elseif ($cat->icon === 'heart') 💊
                            @elseif ($cat->icon === 'gift') 🎁
                            @else 📦 @endif
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-extrabold text-sm text-neutral-900 dark:text-neutral-100 truncate">{{ $cat->nama }}</h3>
                            @if ($budget)
                                <div class="text-[11px] text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5 flex-wrap">
                                    @if ($budget->start_month === $budget->end_month)
                                        <span class="text-sky-600 dark:text-sky-400 font-semibold">Khusus {{ $monthName }}</span>
                                    @else
                                        <span>Rutin (sejak {{ \Illuminate\Support\Carbon::parse($budget->start_month . '-01')->format('M Y') }})</span>
                                    @endif
                                    @if (!empty($budget->keterangan))
                                        <span>•</span>
                                        <span class="italic text-neutral-400 truncate max-w-[120px]">"{{ $budget->keterangan }}"</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-[11px] text-neutral-400 italic">Belum ada batas anggaran</span>
                            @endif
                        </div>
                    </div>

                    {{-- Nominal & Aksi --}}
                    <div class="text-right shrink-0">
                        @if ($limit > 0)
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                Anggaran: <b class="text-neutral-900 dark:text-neutral-100">Rp {{ number_format($limit, 0, ',', '.') }}</b>
                            </div>
                            <div class="font-bold text-xs mt-0.5 {{ $remaining >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 font-extrabold' }}">
                                {{ $remaining >= 0 ? 'Sisa Rp ' . number_format($remaining, 0, ',', '.') : 'Lewat Rp ' . number_format(abs($remaining), 0, ',', '.') }}
                            </div>
                        @else
                            <div class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                                Terpakai Rp {{ number_format($spent, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Progress Bar (jika ada anggaran) --}}
                @if ($limit > 0)
                    <div class="w-full bg-neutral-100 dark:bg-neutral-800 rounded-full h-2.5 overflow-hidden mb-3">
                        <div class="h-full rounded-full transition-all duration-500 {{ $percent > 100 ? 'bg-rose-500' : ($percent >= 80 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                            style="width: {{ min($percent, 100) }}%"></div>
                    </div>
                @endif

                {{-- Footer Kartu --}}
                <div class="flex items-center justify-between pt-2 border-t border-neutral-100 dark:border-neutral-800/80 text-xs">
                    <span class="text-neutral-500 dark:text-neutral-400">
                        Belanja: <b>Rp {{ number_format($spent, 0, ',', '.') }}</b>
                        @if ($limit > 0)
                            <span class="text-neutral-400">({{ $percent }}%)</span>
                        @endif
                    </span>

                    <div class="flex items-center gap-2">
                        @if ($budget)
                            <button type="button" wire:click="edit({{ $budget->id }})"
                                class="text-xs font-semibold text-neutral-600 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white bg-neutral-100 dark:bg-neutral-800 px-2.5 py-1 rounded-lg transition">
                                Ubah
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $budget->id }})"
                                class="text-xs font-semibold text-rose-500 hover:text-rose-700 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1 rounded-lg transition">
                                Hapus
                            </button>
                        @else
                            <button type="button" wire:click="openCreate({{ $cat->id }})"
                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 px-3 py-1 rounded-xl transition">
                                + Pasang Anggaran
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal Atur / Ubah Anggaran --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            x-data x-trap.noscroll="true">
            <div class="bg-white dark:bg-neutral-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 max-h-[92vh] overflow-y-auto">
                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🎯</span>
                        <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100">
                            {{ $isEditing ? 'Ubah Batas Anggaran' : 'Atur Anggaran Belanja' }}
                        </h3>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 p-1">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    {{-- Pilihan Kategori Belanja --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            Kategori Belanja
                        </label>
                        <select wire:model="category_id"
                            class="w-full px-3.5 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            @foreach (\App\Models\Category::where('tipe', 'keluar')->orderBy('urutan')->get() as $c)
                                <option value="{{ $c->id }}">{{ $c->nama }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Input Nominal Anggaran --}}
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
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            Batas Anggaran (Rp)
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-neutral-400 pointer-events-none">Rp</span>
                            <input type="tel" inputmode="numeric" wire:model="nominal"
                                x-on:input="formatRupiah($event.target)"
                                placeholder="0" autofocus
                                class="w-full pl-12 pr-4 py-3.5 text-2xl font-black rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        </div>
                        @error('nominal') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror

                        {{-- Tombol Cepat Nominal (+500rb, +1jt, +2jt, +5jt) --}}
                        <div class="flex items-center gap-1.5 mt-2 overflow-x-auto pb-1 scrollbar-none">
                            <button type="button" wire:click="quickAmount(500000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +500rb
                            </button>
                            <button type="button" wire:click="quickAmount(1000000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +1 Juta
                            </button>
                            <button type="button" wire:click="quickAmount(2000000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +2 Juta
                            </button>
                            <button type="button" wire:click="quickAmount(5000000)"
                                class="px-3 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 hover:bg-neutral-300 dark:hover:bg-neutral-700 text-xs font-bold text-neutral-800 dark:text-neutral-200">
                                +5 Juta
                            </button>
                            <button type="button" wire:click="$set('nominal', '')"
                                class="px-2.5 py-1.5 rounded-xl bg-neutral-200 dark:bg-neutral-800 text-neutral-500 text-xs font-bold ml-auto">
                                Reset
                            </button>
                        </div>
                    </div>

                    {{-- Masa Berlaku / Pilihan Mulai Bulan Kapan --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1.5">
                            Kapan Anggaran Ini Berlaku?
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition {{ $durationType === 'recurring' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/20' : 'border-neutral-200 dark:border-neutral-700' }}">
                                <input type="radio" wire:model.live="durationType" value="recurring" class="text-emerald-600 focus:ring-emerald-500" />
                                <div>
                                    <div class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Mulai bulan ini & seterusnya (Rutin Tiap Bulan)</div>
                                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Berlaku otomatis setiap bulan tanpa perlu diatur ulang.</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition {{ $durationType === 'specific_month' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/20' : 'border-neutral-200 dark:border-neutral-700' }}">
                                <input type="radio" wire:model.live="durationType" value="specific_month" class="text-emerald-600 focus:ring-emerald-500" />
                                <div>
                                    <div class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Khusus bulan {{ \Illuminate\Support\Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y') }} saja</div>
                                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Cocok untuk budget spesial (misal: Lebaran, liburan, arisan tahunan).</div>
                                </div>
                            </label>

                            <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition {{ $durationType === 'custom_start' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 ring-2 ring-emerald-500/20' : 'border-neutral-200 dark:border-neutral-700' }}">
                                <input type="radio" wire:model.live="durationType" value="custom_start" class="text-emerald-600 focus:ring-emerald-500" />
                                <div>
                                    <div class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Mulai dari bulan tertentu di masa depan</div>
                                    <div class="text-[11px] text-neutral-500 dark:text-neutral-400">Pilih sendiri bulan kapan anggaran ini mulai aktif.</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Pemilihan Bulan Mulai (jika custom_start) --}}
                    @if ($durationType === 'custom_start')
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                                Mulai Aktif Pada Bulan:
                            </label>
                            <input type="month" wire:model="start_month"
                                class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                            @error('start_month') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Catatan Opsional --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            Catatan (Boleh Kosong)
                        </label>
                        <input type="text" wire:model="keterangan" placeholder="Contoh: Budget hemat, sudah termasuk jajan anak..."
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="pt-3">
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-xl transition active:scale-[0.98]">
                            <span wire:loading.remove>{{ $isEditing ? 'Simpan Perubahan Anggaran ✨' : 'Pasang Anggaran Belanja 🎉' }}</span>
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
                <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100 mb-1">
                    Hapus Anggaran Ini?
                </h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-5">
                    Batas anggaran untuk kategori ini akan dihapus. Riwayat catatan transaksi belanja tidak akan terhapus.
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="flex-1 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 font-bold text-xs">
                        Batal
                    </button>
                    <button type="button" wire:click="deleteBudget"
                        class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
