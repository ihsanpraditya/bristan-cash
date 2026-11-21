<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Kategori Transaksi')]
class CategoryManager extends Component
{
    /**
     * Active tab: 'keluar' for expense, 'masuk' for income.
     */
    public string $activeTab = 'keluar';

    /**
     * Modal visibility state for creating / editing category.
     */
    public bool $showModal = false;

    /**
     * Modal visibility state for confirming category deletion.
     */
    public bool $showDeleteModal = false;

    /**
     * Whether the modal is currently in edit mode.
     */
    public bool $isEditing = false;

    /**
     * Active category ID being edited.
     */
    public ?int $editingCategoryId = null;

    /**
     * Active category ID targeted for deletion.
     */
    public ?int $targetCategoryId = null;

    /**
     * Error message explaining why a category cannot be deleted.
     */
    public ?string $deleteErrorMessage = null;

    /**
     * Form state: Category name.
     */
    public string $nama = '';

    /**
     * Form state: Category type ('keluar' or 'masuk').
     */
    public string $tipe = 'keluar';

    /**
     * Form state: Category icon identifier.
     */
    public string $icon = 'shopping-cart';

    /**
     * Form state: Sorting order.
     */
    public int $urutan = 1;

    /**
     * Preset icon options with friendly descriptions.
     *
     * @var array<string, array{emoji: string, label: string}>
     */
    public array $presetIcons = [
        'shopping-cart' => ['emoji' => '🛒', 'label' => 'Belanja Dapur / Pasar'],
        'sparkles' => ['emoji' => '🍜', 'label' => 'Makan & Jajan'],
        'academic-cap' => ['emoji' => '🎓', 'label' => 'Anak & Sekolah'],
        'bolt' => ['emoji' => '⚡', 'label' => 'Tagihan Listrik / Air'],
        'truck' => ['emoji' => '🚗', 'label' => 'Bensin & Transport'],
        'home' => ['emoji' => '🏠', 'label' => 'Kebutuhan Rumah'],
        'heart' => ['emoji' => '💊', 'label' => 'Kesehatan & Obat'],
        'banknotes' => ['emoji' => '💵', 'label' => 'Gaji / Nafkah'],
        'gift' => ['emoji' => '🎁', 'label' => 'Bonus / THR / Hadiah'],
        'arrow-trending-up' => ['emoji' => '📈', 'label' => 'Pendapatan / Usaha'],
        'ellipsis-horizontal-circle' => ['emoji' => '📦', 'label' => 'Lain-lain'],
    ];

    /**
     * Switch tab between expense and income categories.
     */
    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['keluar', 'masuk']) ? $tab : 'keluar';
    }

    /**
     * Open modal to create a new category.
     */
    public function openCreate(?string $tipe = null): void
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->editingCategoryId = null;
        $this->nama = '';
        $this->tipe = $tipe ?? $this->activeTab;
        $this->icon = $this->tipe === 'masuk' ? 'banknotes' : 'shopping-cart';

        $maxUrutan = Category::where('tipe', $this->tipe)->max('urutan') ?? 0;
        $this->urutan = $maxUrutan + 1;

        $this->showModal = true;
    }

    /**
     * Open modal to edit an existing category.
     */
    public function edit(int $id): void
    {
        $this->resetValidation();
        $category = Category::findOrFail($id);

        $this->isEditing = true;
        $this->editingCategoryId = $category->id;
        $this->nama = $category->nama;
        $this->tipe = $category->tipe;
        $this->icon = $category->icon ?? 'shopping-cart';
        $this->urutan = $category->urutan;

        $this->showModal = true;
    }

    /**
     * Save newly created or edited category.
     */
    public function save(): void
    {
        $rules = [
            'nama' => ['required', 'string', 'max:50'],
            'tipe' => ['required', 'in:keluar,masuk'],
            'icon' => ['required', 'string', 'max:50'],
            'urutan' => ['required', 'integer', 'min:1'],
        ];

        $validated = $this->validate($rules, [
            'nama.required' => 'Nama kategori wajib diisi ya.',
            'nama.max' => 'Nama kategori maksimal 50 huruf.',
            'tipe.required' => 'Pilih jenis kategori (pengeluaran atau pemasukan).',
        ]);

        if ($this->isEditing && $this->editingCategoryId !== null) {
            $category = Category::findOrFail($this->editingCategoryId);
            $category->update($validated);
            session()->flash('toast', "Kategori '{$category->nama}' berhasil diperbarui! ✨");
        } else {
            $category = Category::create($validated);
            session()->flash('toast', "Kategori '{$category->nama}' berhasil ditambahkan! 🎉");
        }

        $this->activeTab = $this->tipe;
        $this->showModal = false;
        $this->reset(['isEditing', 'editingCategoryId', 'nama']);
    }

    /**
     * Prompt delete confirmation with transaction safety check.
     */
    public function confirmDelete(int $id): void
    {
        $this->targetCategoryId = $id;

        $transactionCount = Transaction::where('category_id', $id)->count();

        if ($transactionCount > 0) {
            $this->deleteErrorMessage = "Kategori ini masih digunakan pada {$transactionCount} catatan transaksi. Agar catatan keuangan tidak berantakan, kategori ini tidak bisa dihapus langsung.";
        } else {
            $this->deleteErrorMessage = null;
        }

        $this->showDeleteModal = true;
    }

    /**
     * Delete category if safe.
     */
    public function deleteCategory(): void
    {
        if ($this->deleteErrorMessage !== null) {
            $this->showDeleteModal = false;

            return;
        }

        if ($this->targetCategoryId !== null) {
            $category = Category::find($this->targetCategoryId);
            if ($category !== null) {
                $nama = $category->nama;
                $category->delete();
                session()->flash('toast', "Kategori '{$nama}' berhasil dihapus.");
            }
        }

        $this->showDeleteModal = false;
        $this->targetCategoryId = null;
    }

    /**
     * Render the Livewire component.
     */
    public function render(): View
    {
        $categories = Category::where('tipe', $this->activeTab)
            ->withCount('transactions')
            ->orderBy('urutan')
            ->get();

        $keluarCount = Category::where('tipe', 'keluar')->count();
        $masukCount = Category::where('tipe', 'masuk')->count();

        return view('livewire.category-manager', [
            'categories' => $categories,
            'keluarCount' => $keluarCount,
            'masukCount' => $masukCount,
        ]);
    }
}
