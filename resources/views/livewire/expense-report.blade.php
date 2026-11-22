<div class="max-w-2xl mx-auto px-4 pt-4 pb-24 min-h-screen relative">
    {{-- Header & Navigasi Kembali --}}
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('dashboard') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-xs font-bold text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 bg-neutral-100 dark:bg-neutral-800 px-3 py-2 rounded-xl transition">
            <span>←</span>
            <span>Kembali ke Catatan</span>
        </a>

        <a href="{{ route('categories.index') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 px-3 py-2 rounded-xl transition">
            <span>🏷️</span>
            <span>Kelola Pos</span>
        </a>
    </div>

    {{-- Judul Halaman --}}
    <div class="mb-5">
        <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-100 tracking-tight">Analisis Pos Belanja</h1>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            Pantau sebaran pengeluaran keluarga berdasarkan pos belanja dan rincian transaksinya.
        </p>
    </div>

    {{-- Filter Periode Waktu (Pills Ramah Mobile) --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-5 no-scrollbar text-xs font-semibold">
        @php
            $periods = [
                'bulan_ini' => 'Bulan Ini',
                'bulan_lalu' => 'Bulan Lalu',
                'tiga_bulan' => '3 Bulan',
                'tahun_ini' => 'Tahun Ini',
                'semua' => 'Semua',
            ];
        @endphp
        @foreach ($periods as $key => $label)
            <button type="button" wire:click="setPeriod('{{ $key }}')"
                class="px-3.5 py-2 rounded-xl whitespace-nowrap transition active:scale-95 {{ $period === $key ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 font-bold shadow-sm' : 'bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-neutral-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Kartu Ringkasan Utama (Metric Cards) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        {{-- Total Belanja --}}
        <div class="col-span-2 sm:col-span-1 bg-gradient-to-br from-rose-500 to-rose-600 text-white rounded-2xl p-4 shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-[11px] font-semibold text-rose-100 uppercase tracking-wider block">Total Pengeluaran</span>
                <div class="text-xl font-black mt-1 tracking-tight">
                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                </div>
            </div>
            <div class="text-[10px] text-rose-100/90 mt-2 flex items-center gap-1">
                <span>📅</span>
                <span>{{ $periodLabel }}</span>
            </div>
        </div>

        {{-- Pos Terbesar --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-3.5 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider block">Pos Terbesar</span>
            @if ($topCategory)
                @php
                    $topIcon = $presetIcons[$topCategory['icon']] ?? ['emoji' => '📌'];
                @endphp
                <div class="my-1">
                    <div class="text-sm font-black text-neutral-900 dark:text-neutral-100 truncate flex items-center gap-1">
                        <span>{{ $topIcon['emoji'] }}</span>
                        <span class="truncate">{{ $topCategory['nama'] }}</span>
                    </div>
                    <div class="text-xs font-extrabold text-rose-600 dark:text-rose-400 mt-0.5">
                        {{ $topCategory['percentage'] }}% dari total
                    </div>
                </div>
                <div class="text-[10px] text-neutral-400">
                    Rp {{ number_format($topCategory['sum'], 0, ',', '.') }}
                </div>
            @else
                <div class="my-auto py-2 text-xs text-neutral-400 italic">
                    Belum ada data
                </div>
            @endif
        </div>

        {{-- Frekuensi Belanja --}}
        <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-3.5 shadow-sm flex flex-col justify-between">
            <span class="text-[10px] font-bold text-neutral-500 uppercase tracking-wider block">Catatan Belanja</span>
            <div class="my-1">
                <div class="text-lg font-black text-neutral-900 dark:text-neutral-100">
                    {{ $totalTransactionsCount }}
                    <span class="text-xs font-semibold text-neutral-500">transaksi</span>
                </div>
                <div class="text-[10px] text-neutral-400 mt-0.5">
                    @if ($totalTransactionsCount > 0)
                        Rata-rata: Rp {{ number_format((int) ($totalExpense / $totalTransactionsCount), 0, ',', '.') }}
                    @else
                        Belum ada belanja
                    @endif
                </div>
            </div>
            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                <span>🛍️</span>
                <span>{{ count(array_filter($categoryBreakdown, fn($c) => $c['sum'] > 0)) }} pos aktif</span>
            </div>
        </div>
    </div>

    {{-- Bagan Donut Chart & Komposisi Pos --}}
    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-3xl p-5 shadow-sm mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-extrabold text-neutral-900 dark:text-neutral-100 tracking-tight">Komposisi Pengeluaran</h2>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">Persentase pengeluaran per pos belanja</p>
            </div>
            @if ($selectedCategoryId)
                <button type="button" wire:click="clearCategory"
                    class="text-[11px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1 rounded-lg hover:underline transition">
                    ✕ Reset Filter
                </button>
            @endif
        </div>

        @if ($totalExpense > 0 && !empty($donutSegments))
            <div class="flex flex-col sm:flex-row items-center gap-6 justify-center py-2">
                {{-- SVG Donut Chart --}}
                <div class="relative size-44 shrink-0 flex items-center justify-center">
                    <svg viewBox="0 0 42 42" class="size-full -rotate-90">
                        {{-- Background Ring --}}
                        <circle cx="21" cy="21" r="15.91549430918954" fill="transparent"
                            class="stroke-neutral-100 dark:stroke-neutral-800" stroke-width="4.5"></circle>

                        {{-- Donut Segments --}}
                        @foreach ($donutSegments as $segment)
                            <circle cx="21" cy="21" r="15.91549430918954" fill="transparent"
                                stroke="{{ $segment['color'] }}" stroke-width="4.5"
                                stroke-dasharray="{{ $segment['dasharray'] }}"
                                stroke-dashoffset="{{ $segment['dashoffset'] }}"
                                class="cursor-pointer transition-all duration-300 hover:opacity-80 {{ $selectedCategoryId === $segment['id'] ? 'stroke-[5.5]' : '' }}"
                                wire:click="selectCategory({{ $segment['id'] }})">
                                <title>{{ $segment['nama'] }}: {{ $segment['percentage'] }}%</title>
                            </circle>
                        @endforeach
                    </svg>

                    {{-- Inside Donut Content --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none px-3">
                        @if ($activeCategory)
                            @php
                                $actIcon = $presetIcons[$activeCategory->icon] ?? ['emoji' => '📌'];
                            @endphp
                            <span class="text-2xl leading-none">{{ $actIcon['emoji'] }}</span>
                            <span class="text-[11px] font-bold text-neutral-800 dark:text-neutral-200 truncate max-w-[100px] mt-0.5">
                                {{ $activeCategory->nama }}
                            </span>
                        @else
                            <span class="text-[10px] font-semibold text-neutral-400 uppercase tracking-wider">Total</span>
                            <span class="text-xs font-black text-neutral-900 dark:text-neutral-100 mt-0.5 leading-tight">
                                Rp {{ number_format($totalExpense, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Legenda & Mini Bar Chart --}}
                <div class="w-full space-y-2 max-h-56 overflow-y-auto pr-1">
                    @foreach ($categoryBreakdown as $cat)
                        @if ($cat['sum'] > 0)
                            @php
                                $iconData = $presetIcons[$cat['icon']] ?? ['emoji' => '📌'];
                                $isSelected = $selectedCategoryId === $cat['id'];
                            @endphp
                            <button type="button" wire:click="selectCategory({{ $cat['id'] }})"
                                class="w-full text-left p-2 rounded-xl transition flex items-center justify-between gap-3 {{ $isSelected ? 'bg-neutral-100 dark:bg-neutral-800 ring-2 ring-neutral-400 dark:ring-neutral-600' : 'hover:bg-neutral-50 dark:hover:bg-neutral-800/60' }}">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="size-3 rounded-full shrink-0" style="background-color: {{ $cat['color'] }};"></span>
                                    <span class="text-sm shrink-0">{{ $iconData['emoji'] }}</span>
                                    <span class="text-xs font-bold text-neutral-800 dark:text-neutral-200 truncate {{ $isSelected ? 'text-neutral-900 dark:text-white' : '' }}">
                                        {{ $cat['nama'] }}
                                    </span>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-xs font-black text-neutral-900 dark:text-neutral-100">
                                        {{ $cat['percentage'] }}%
                                    </div>
                                    <div class="text-[10px] text-neutral-400">
                                        Rp {{ number_format($cat['sum'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        @else
            <div class="py-10 text-center">
                <span class="text-4xl block mb-2">📊</span>
                <p class="font-bold text-sm text-neutral-800 dark:text-neutral-200">Belum ada pengeluaran pada periode ini</p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Catat transaksi pengeluaran untuk melihat bagan analisis.</p>
            </div>
        @endif
    </div>

    {{-- Daftar Rincian COA (Pos Pengeluaran) --}}
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base font-extrabold text-neutral-900 dark:text-neutral-100 tracking-tight">
                Daftar Pos Pengeluaran
            </h2>
            <span class="text-xs text-neutral-500">Urut dari terbesar</span>
        </div>

        <div class="space-y-2.5">
            @forelse ($categoryBreakdown as $cat)
                @php
                    $iconData = $presetIcons[$cat['icon']] ?? ['emoji' => '📌'];
                    $isSelected = $selectedCategoryId === $cat['id'];
                @endphp
                <div wire:click="selectCategory({{ $cat['id'] }})"
                    class="cursor-pointer bg-white dark:bg-neutral-900 border rounded-2xl p-3.5 shadow-sm transition hover:border-neutral-300 dark:hover:border-neutral-700 {{ $isSelected ? 'border-neutral-900 dark:border-white ring-2 ring-neutral-900/10 dark:ring-white/10' : 'border-neutral-200 dark:border-neutral-800' }}">
                    <div class="flex items-center justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="size-9 rounded-xl flex items-center justify-center text-lg bg-neutral-100 dark:bg-neutral-800 shrink-0">
                                {{ $iconData['emoji'] }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-xs text-neutral-900 dark:text-neutral-100 truncate flex items-center gap-1.5">
                                    <span>{{ $cat['nama'] }}</span>
                                    @if ($isSelected)
                                        <span class="bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 text-[9px] px-1.5 py-0.2 rounded-full font-bold">Terpilih</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-neutral-400 mt-0.5">
                                    {{ $cat['count'] }} transaksi
                                </div>
                            </div>
                        </div>

                        <div class="text-right shrink-0">
                            <div class="font-black text-xs text-neutral-900 dark:text-neutral-100">
                                Rp {{ number_format($cat['sum'], 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] font-bold text-neutral-500 dark:text-neutral-400">
                                {{ $cat['percentage'] }}%
                            </div>
                        </div>
                    </div>

                    {{-- Horizontal Progress Bar --}}
                    <div class="w-full bg-neutral-100 dark:bg-neutral-800 h-1.5 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500"
                            style="width: {{ $cat['percentage'] }}%; background-color: {{ $cat['color'] }};">
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-2xl p-6 text-center text-xs text-neutral-500">
                    Belum ada pos pengeluaran.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Daftar Transaksi pada COA Terpilih --}}
    <div>
        <div class="flex items-center justify-between mb-3">
            <div>
                <h2 class="text-base font-extrabold text-neutral-900 dark:text-neutral-100 tracking-tight">
                    @if ($activeCategory)
                        Rincian: {{ $activeCategory->nama }}
                    @else
                        Semua Transaksi Belanja
                    @endif
                </h2>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
                    @if ($activeCategory)
                        Menampilkan transaksi khusus pos belanja ini
                    @else
                        Pilih salah satu pos di atas untuk memfilter rincian
                    @endif
                </p>
            </div>

            @if ($selectedCategoryId)
                <button type="button" wire:click="clearCategory"
                    class="text-xs font-semibold text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200 bg-neutral-100 dark:bg-neutral-800 px-3 py-1.5 rounded-xl transition">
                    Lihat Semua Pos
                </button>
            @endif
        </div>

        {{-- List Transaksi --}}
        <div class="space-y-2">
            @forelse ($transactions as $trx)
                @php
                    $trxCategory = $trx->category;
                    $trxIcon = $trxCategory ? ($presetIcons[$trxCategory->icon] ?? ['emoji' => '🛒']) : ['emoji' => '💸'];
                @endphp
                <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-3 shadow-sm flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="size-10 rounded-xl bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 flex items-center justify-center text-lg shrink-0">
                            {{ $trxIcon['emoji'] }}
                        </div>

                        <div class="min-w-0">
                            <div class="font-bold text-xs text-neutral-900 dark:text-neutral-100 truncate">
                                {{ $trx->keterangan ?: ($trxCategory ? $trxCategory->nama : 'Pengeluaran') }}
                            </div>
                            <div class="text-[10px] text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5 mt-0.5">
                                <span>📅 {{ \Carbon\Carbon::parse($trx->tanggal)->translatedFormat('d M Y') }}</span>
                                <span>•</span>
                                <span>💳 {{ $trx->account?->nama ?? 'Dompet' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="font-black text-xs text-rose-600 dark:text-rose-400">
                            -Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                        </div>
                        @if (!$selectedCategoryId && $trxCategory)
                            <div class="text-[9px] font-semibold text-neutral-400 mt-0.5 truncate max-w-[90px]">
                                {{ $trxCategory->nama }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-2xl p-8 text-center">
                    <span class="text-3xl block mb-2">🧾</span>
                    <p class="font-bold text-xs text-neutral-800 dark:text-neutral-200">
                        @if ($activeCategory)
                            Belum ada catatan belanja pada pos {{ $activeCategory->nama }}
                        @else
                            Belum ada transaksi belanja pada periode ini
                        @endif
                    </p>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">
                        Transaksi pengeluaran baru akan muncul di sini.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($transactions->hasPages())
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
