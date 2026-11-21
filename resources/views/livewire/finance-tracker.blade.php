<div class="max-w-2xl mx-auto px-4 pt-4 pb-28 min-h-screen relative" x-data="offlineQueueHandler()">
    {{-- Header & Notifikasi --}}
    @include('livewire.finance-tracker.header')

    {{-- Saldo Utama & Ringkasan Cashflow --}}
    @include('livewire.finance-tracker.balance-card')

    {{-- Kartu Rekening / Dompet (Scroll Horizontal) --}}
    @include('livewire.finance-tracker.account-cards')

    {{-- Filter Periode & Kategori --}}
    @include('livewire.finance-tracker.filters')

    {{-- Riwayat Catatan Transaksi --}}
    @include('livewire.finance-tracker.transaction-list')

    {{-- Bottom Sticky Action Bar (Jempol-Friendly) --}}
    @include('livewire.finance-tracker.bottom-bar')

    {{-- Modal / Bottom Sheet Input Transaksi --}}
    @include('livewire.finance-tracker.input-modal')

    {{-- Modal Konfirmasi Hapus --}}
    @include('livewire.finance-tracker.delete-modal')
</div>

{{-- Alpine.js Offline Queue Component Script --}}
@include('livewire.finance-tracker.offline-script')
