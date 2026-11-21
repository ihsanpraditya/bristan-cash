<?php

namespace App\Livewire;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Anggaran Belanja Bulanan')]
class BudgetManager extends Component
{
    /**
     * Currently viewed month ('YYYY-MM').
     */
    public string $selectedMonth = '';

    /**
     * Modal visibility state for setting a budget.
     */
    public bool $showModal = false;

    /**
     * Modal visibility state for confirming budget deletion.
     */
    public bool $showDeleteModal = false;

    /**
     * Target budget ID for deletion.
     */
    public ?int $targetBudgetId = null;

    /**
     * Whether the modal is in edit mode.
     */
    public bool $isEditing = false;

    /**
     * Active budget ID being edited.
     */
    public ?int $editingBudgetId = null;

    /**
     * Form state: Category ID.
     */
    public ?int $category_id = null;

    /**
     * Form state: Budget limit amount (formatted string).
     */
    public string $nominal = '';

    /**
     * Form state: Duration mode:
     * - 'recurring': Mulai bulan terpilih & berlaku seterusnya
     * - 'specific_month': Hanya berlaku khusus bulan terpilih
     * - 'custom_start': Mulai bulan tertentu di masa depan
     */
    public string $durationType = 'recurring';

    /**
     * Form state: Month from which budget applies ('YYYY-MM').
     */
    public string $start_month = '';

    /**
     * Form state: Optional note.
     */
    public ?string $keterangan = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->start_month = $this->selectedMonth;
    }

    /**
     * Navigate to previous month.
     */
    public function previousMonth(): void
    {
        $this->selectedMonth = Carbon::parse($this->selectedMonth.'-01')->subMonth()->format('Y-m');
    }

    /**
     * Navigate to next month.
     */
    public function nextMonth(): void
    {
        $this->selectedMonth = Carbon::parse($this->selectedMonth.'-01')->addMonth()->format('Y-m');
    }

    /**
     * Reset to current calendar month.
     */
    public function setThisMonth(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    /**
     * Append quick amount or set directly.
     */
    public function quickAmount(int $val): void
    {
        $current = (int) preg_replace('/[^0-9]/', '', (string) $this->nominal);
        $new = $current + $val;
        $this->nominal = number_format($new, 0, ',', '.');
    }

    /**
     * Open modal to create or customize budget for a category.
     */
    public function openCreate(?int $categoryId = null): void
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->editingBudgetId = null;

        $firstCat = Category::where('tipe', 'keluar')->orderBy('urutan')->first();
        $this->category_id = $categoryId ?? $firstCat?->id;
        $this->nominal = '';
        $this->durationType = 'recurring';
        $this->start_month = $this->selectedMonth;
        $this->keterangan = null;

        $this->showModal = true;
    }

    /**
     * Open modal to edit an existing budget record.
     */
    public function edit(int $id): void
    {
        $this->resetValidation();
        $budget = Budget::findOrFail($id);

        $this->isEditing = true;
        $this->editingBudgetId = $budget->id;
        $this->category_id = $budget->category_id;
        $this->nominal = number_format($budget->nominal, 0, ',', '.');
        $this->start_month = $budget->start_month;
        $this->keterangan = $budget->keterangan;

        if ($budget->start_month === $budget->end_month && ! empty($budget->end_month)) {
            $this->durationType = 'specific_month';
        } else {
            $this->durationType = 'recurring';
        }

        $this->showModal = true;
    }

    /**
     * Save new or updated budget.
     */
    public function save(): void
    {
        $cleanedNominal = (int) preg_replace('/[^0-9]/', '', (string) $this->nominal);
        $nominalForValidation = $cleanedNominal > 0 ? $cleanedNominal : null;

        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'nominal' => ['required', 'integer', 'min:1000'],
            'start_month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'durationType' => ['required', 'in:recurring,specific_month,custom_start'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ];

        $validator = validator([
            'category_id' => $this->category_id,
            'nominal' => $nominalForValidation,
            'start_month' => $this->start_month,
            'durationType' => $this->durationType,
            'keterangan' => $this->keterangan,
        ], $rules, [
            'nominal.required' => 'Nominal anggaran wajib diisi ya.',
            'nominal.min' => 'Nominal anggaran minimal Rp 1.000.',
            'category_id.required' => 'Pilih kategori belanja.',
            'start_month.required' => 'Pilih bulan mulai berlakunya anggaran.',
        ]);

        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag());

            return;
        }

        $endMonth = $this->durationType === 'specific_month' ? $this->start_month : null;

        $data = [
            'category_id' => $this->category_id,
            'nominal' => $cleanedNominal,
            'start_month' => $this->start_month,
            'end_month' => $endMonth,
            'keterangan' => $this->keterangan,
        ];

        if ($this->isEditing && $this->editingBudgetId !== null) {
            $budget = Budget::findOrFail($this->editingBudgetId);
            $budget->update($data);
            session()->flash('toast', "Anggaran '{$budget->category?->nama}' berhasil diperbarui! ✨");
        } else {
            $budget = Budget::create($data);
            session()->flash('toast', "Anggaran '{$budget->category?->nama}' berhasil dibuat! 🎉");
        }

        $this->showModal = false;
        $this->reset(['isEditing', 'editingBudgetId', 'nominal', 'keterangan']);
    }

    /**
     * Prompt delete confirmation.
     */
    public function confirmDelete(int $id): void
    {
        $this->targetBudgetId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Delete active budget.
     */
    public function deleteBudget(): void
    {
        if ($this->targetBudgetId !== null) {
            $budget = Budget::find($this->targetBudgetId);
            if ($budget !== null) {
                $categoryName = $budget->category?->nama ?? 'kategori';
                $budget->delete();
                session()->flash('toast', "Anggaran untuk {$categoryName} telah dihapus.");
            }
        }

        $this->showDeleteModal = false;
        $this->targetBudgetId = null;
    }

    /**
     * Render the Livewire component.
     */
    public function render(): View
    {
        $categories = Category::where('tipe', 'keluar')
            ->orderBy('urutan')
            ->get();

        $monthDate = Carbon::parse($this->selectedMonth.'-01');
        $monthStart = $monthDate->copy()->startOfMonth()->toDateString();
        $monthEnd = $monthDate->copy()->endOfMonth()->toDateString();
        $monthName = $monthDate->translatedFormat('F Y');

        $budgetItems = [];
        $totalAnggaran = 0;
        $totalTerpakai = 0;

        foreach ($categories as $cat) {
            $budget = Budget::forCategoryAndMonth($cat->id, $this->selectedMonth);

            $spent = (int) Transaction::where('category_id', $cat->id)
                ->where('type', 'keluar')
                ->whereBetween('tanggal', [$monthStart, $monthEnd])
                ->sum('nominal');

            $limit = $budget?->nominal ?? 0;
            $percent = $limit > 0 ? (int) round(($spent / $limit) * 100) : 0;
            $remaining = $limit - $spent;

            if ($limit > 0) {
                $totalAnggaran += $limit;
            }
            $totalTerpakai += $spent;

            $budgetItems[] = [
                'category' => $cat,
                'budget' => $budget,
                'limit' => $limit,
                'spent' => $spent,
                'remaining' => $remaining,
                'percent' => $percent,
            ];
        }

        $sisaAnggaran = $totalAnggaran - $totalTerpakai;
        $overallPercent = $totalAnggaran > 0 ? (int) round(($totalTerpakai / $totalAnggaran) * 100) : 0;

        return view('livewire.budget-manager', [
            'budgetItems' => $budgetItems,
            'totalAnggaran' => $totalAnggaran,
            'totalTerpakai' => $totalTerpakai,
            'sisaAnggaran' => $sisaAnggaran,
            'overallPercent' => $overallPercent,
            'monthName' => $monthName,
            'isCurrentMonth' => $this->selectedMonth === now()->format('Y-m'),
        ]);
    }
}
