{{-- Saldo Utama & Ringkasan Cashflow --}}
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
