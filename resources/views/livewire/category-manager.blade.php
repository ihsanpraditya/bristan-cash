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

        <button type="button" wire:click="openCreate('{{ $activeTab }}')"
            class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-md transition">
            <span>+</span>
            <span>Tambah Kategori</span>
        </button>
    </div>

    {{-- Judul Halaman --}}
    <div class="mb-5">
        <h1 class="text-2xl font-black text-neutral-900 dark:text-neutral-100 tracking-tight">Kategori Catatan Keuangan</h1>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
            Pos-pos belanja harian keluarga dan sumber pemasukan agar pengeluaran mudah dipantau.
        </p>
    </div>

    {{-- Tab Switcher: Pengeluaran vs Pemasukan --}}
    <div class="flex bg-neutral-100 dark:bg-neutral-800 p-1 rounded-2xl mb-5 text-xs font-bold">
        <button type="button" wire:click="setTab('keluar')"
            class="flex-1 py-2.5 rounded-xl transition flex items-center justify-center gap-1.5 {{ $activeTab === 'keluar' ? 'bg-rose-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}">
            <span>🛒</span>
            <span>Pengeluaran</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeTab === 'keluar' ? 'bg-white/20' : 'bg-neutral-200 dark:bg-neutral-700' }}">{{ $keluarCount }}</span>
        </button>
        <button type="button" wire:click="setTab('masuk')"
            class="flex-1 py-2.5 rounded-xl transition flex items-center justify-center gap-1.5 {{ $activeTab === 'masuk' ? 'bg-emerald-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}">
            <span>💰</span>
            <span>Pemasukan</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeTab === 'masuk' ? 'bg-white/20' : 'bg-neutral-200 dark:bg-neutral-700' }}">{{ $masukCount }}</span>
        </button>
    </div>

    {{-- Daftar Kategori --}}
    <div class="space-y-2.5">
        @forelse ($categories as $cat)
            @php
                $iconData = $presetIcons[$cat->icon] ?? ['emoji' => '📌', 'label' => 'Kategori'];
            @endphp
            <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-3.5 shadow-sm flex items-center justify-between gap-3 hover:border-neutral-300 dark:hover:border-neutral-700 transition">
                <div class="flex items-center gap-3 min-w-0">
                    {{-- Icon Emoji --}}
                    <div class="size-11 shrink-0 rounded-2xl flex items-center justify-center text-xl {{ $cat->tipe === 'masuk' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60' : 'bg-rose-50 text-rose-700 dark:bg-rose-950/60' }}">
                        {{ $iconData['emoji'] }}
                    </div>

                    {{-- Nama Kategori --}}
                    <div class="min-w-0">
                        <div class="font-bold text-sm text-neutral-900 dark:text-neutral-100 truncate">
                            {{ $cat->nama }}
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 flex items-center gap-2">
                            <span>Pos #{{ $cat->urutan }}</span>
                            <span>•</span>
                            <span class="{{ $cat->transactions_count > 0 ? 'text-neutral-600 dark:text-neutral-300 font-medium' : 'text-neutral-400' }}">
                                {{ $cat->transactions_count }} catatan
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" wire:click="edit({{ $cat->id }})"
                        class="text-xs font-semibold text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100 bg-neutral-100 dark:bg-neutral-800 px-3 py-1.5 rounded-xl transition">
                        Ubah
                    </button>
                    <button type="button" wire:click="confirmDelete({{ $cat->id }})"
                        class="text-xs font-semibold text-rose-500 hover:text-rose-700 bg-rose-50 dark:bg-rose-950/40 px-3 py-1.5 rounded-xl transition">
                        Hapus
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-neutral-900 border border-dashed border-neutral-300 dark:border-neutral-800 rounded-3xl p-8 text-center">
                <span class="text-4xl block mb-2">{{ $activeTab === 'masuk' ? '💰' : '🛒' }}</span>
                <p class="font-bold text-sm text-neutral-800 dark:text-neutral-200">
                    Belum ada kategori {{ $activeTab === 'masuk' ? 'pemasukan' : 'pengeluaran' }}
                </p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 mb-4">
                    Tambahkan kategori agar pencatatan keuangan lebih rapi.
                </p>
                <button type="button" wire:click="openCreate('{{ $activeTab }}')"
                    class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md">
                    + Tambah Kategori
                </button>
            </div>
        @endforelse
    </div>

    {{-- Modal Tambah / Ubah Kategori --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
            x-data x-trap.noscroll="true">
            <div class="bg-white dark:bg-neutral-900 w-full max-w-lg rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl border border-neutral-200 dark:border-neutral-800 max-h-[92vh] overflow-y-auto">
                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-3 border-b border-neutral-100 dark:border-neutral-800 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🏷️</span>
                        <h3 class="font-extrabold text-base text-neutral-900 dark:text-neutral-100">
                            {{ $isEditing ? 'Ubah Kategori' : 'Tambah Kategori Baru' }}
                        </h3>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 p-1">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-4">
                    {{-- Tipe Kategori --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1.5">
                            Jenis Kategori
                        </label>
                        <div class="flex bg-neutral-100 dark:bg-neutral-800 p-1 rounded-2xl text-xs font-bold">
                            <button type="button" wire:click="$set('tipe', 'keluar')"
                                class="flex-1 py-2 rounded-xl transition {{ $tipe === 'keluar' ? 'bg-rose-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400' }}">
                                Pengeluaran (Belanja)
                            </button>
                            <button type="button" wire:click="$set('tipe', 'masuk')"
                                class="flex-1 py-2 rounded-xl transition {{ $tipe === 'masuk' ? 'bg-emerald-600 text-white shadow-sm' : 'text-neutral-600 dark:text-neutral-400' }}">
                                Pemasukan (Uang Masuk)
                            </button>
                        </div>
                    </div>

                    {{-- Nama Kategori --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            Nama Kategori
                        </label>
                        <input type="text" wire:model="nama" placeholder="Contoh: Skincare, Arisan, Sedekah, Hiburan..." autofocus
                            class="w-full px-3.5 py-3 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        @error('nama') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Pilihan Ikon Emoji --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1.5">
                            Pilih Ikon Lambang
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1">
                            @foreach ($presetIcons as $iconKey => $data)
                                <button type="button" wire:click="$set('icon', '{{ $iconKey }}')"
                                    class="p-2.5 rounded-2xl border flex items-center gap-2 text-left transition {{ $icon === $iconKey ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200 ring-2 ring-emerald-500/20' : 'border-neutral-200 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800' }}">
                                    <span class="text-lg shrink-0">{{ $data['emoji'] }}</span>
                                    <span class="text-xs font-semibold truncate">{{ $data['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Urutan Tampilan --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-neutral-500 mb-1">
                            Nomor Urutan
                        </label>
                        <input type="number" wire:model="urutan" min="1"
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                        <p class="text-[11px] text-neutral-400 mt-1">Nomor lebih kecil akan tampil paling atas saat memilih kategori.</p>
                        @error('urutan') <p class="text-rose-500 text-xs mt-1 font-medium">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="pt-3">
                        <button type="submit" wire:loading.attr="disabled"
                            class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-xl transition active:scale-[0.98]">
                            <span wire:loading.remove>{{ $isEditing ? 'Simpan Perubahan Kategori ✨' : 'Simpan Kategori Baru 🎉' }}</span>
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
                    {{ $deleteErrorMessage ? 'Tidak Bisa Dihapus' : 'Hapus Kategori Ini?' }}
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
                        Kategori ini akan dihapus dari pilihan. Catatan lain tidak akan terpengaruh.
                    </p>
                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="$set('showDeleteModal', false)"
                            class="flex-1 py-2.5 rounded-xl border border-neutral-300 dark:border-neutral-700 text-neutral-700 dark:text-neutral-300 font-bold text-xs">
                            Batal
                        </button>
                        <button type="button" wire:click="deleteCategory"
                            class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md">
                            Hapus
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
