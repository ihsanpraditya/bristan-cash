<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Analisis Belanja & Pos Pengeluaran')]
class ExpenseReport extends Component
{
    use WithPagination;

    /**
     * Selected time filter period:
     * 'bulan_ini' | 'bulan_lalu' | 'tiga_bulan' | 'tahun_ini' | 'semua'
     */
    #[Url]
    public string $period = 'bulan_ini';

    /**
     * Filter by selected Category ID (null means all categories).
     */
    #[Url(as: 'kategori')]
    public ?int $selectedCategoryId = null;

    /**
     * Preset icon options matching CategoryManager.
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
     * Color palette for chart segments and badges.
     *
     * @var list<string>
     */
    public array $palette = [
        '#f43f5e', // rose-500
        '#3b82f6', // blue-500
        '#10b981', // emerald-500
        '#f59e0b', // amber-500
        '#8b5cf6', // violet-500
        '#06b6d4', // cyan-500
        '#ec4899', // pink-500
        '#14b8a6', // teal-500
        '#f97316', // orange-500
        '#84cc16', // lime-500
        '#6366f1', // indigo-500
        '#64748b', // slate-500
    ];

    /**
     * Set the active period filter.
     */
    public function setPeriod(string $period): void
    {
        $validPeriods = ['bulan_ini', 'bulan_lalu', 'tiga_bulan', 'tahun_ini', 'semua'];

        if (in_array($period, $validPeriods, true)) {
            $this->period = $period;
            $this->resetPage();
        }
    }

    /**
     * Toggle or select a specific category.
     */
    public function selectCategory(?int $categoryId): void
    {
        if ($this->selectedCategoryId === $categoryId) {
            $this->selectedCategoryId = null;
        } else {
            $this->selectedCategoryId = $categoryId;
        }

        $this->resetPage();
    }

    /**
     * Reset selected category to show all.
     */
    public function clearCategory(): void
    {
        $this->selectedCategoryId = null;
        $this->resetPage();
    }

    /**
     * Resolve date boundaries based on selected period.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function getDateRange(): array
    {
        return match ($this->period) {
            'bulan_lalu' => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ],
            'tiga_bulan' => [
                now()->subMonths(2)->startOfMonth(),
                now()->endOfMonth(),
            ],
            'tahun_ini' => [
                now()->startOfYear(),
                now()->endOfYear(),
            ],
            'semua' => [
                null,
                null,
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ],
        };
    }

    /**
     * Get human-readable period label.
     */
    protected function getPeriodLabel(): string
    {
        return match ($this->period) {
            'bulan_lalu' => now()->subMonth()->translatedFormat('F Y'),
            'tiga_bulan' => '3 Bulan Terakhir',
            'tahun_ini' => 'Tahun '.now()->format('Y'),
            'semua' => 'Semua Catatan',
            default => now()->translatedFormat('F Y'),
        };
    }

    /**
     * Render the Livewire component.
     */
    public function render(): View
    {
        [$startDate, $endDate] = $this->getDateRange();

        // 1. Calculate overall expenses
        $totalExpenseQuery = Transaction::query()
            ->where('type', 'keluar')
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]));

        $totalExpense = (int) $totalExpenseQuery->sum('nominal');
        $totalTransactionsCount = (int) $totalExpenseQuery->count();

        // 2. Fetch categories with expenses in current period
        $categoriesRaw = Category::query()
            ->where('tipe', 'keluar')
            ->withSum(['transactions' => function ($query) use ($startDate, $endDate) {
                $query->where('type', 'keluar')
                    ->when($startDate && $endDate, fn ($q) => $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]));
            }], 'nominal')
            ->withCount(['transactions' => function ($query) use ($startDate, $endDate) {
                $query->where('type', 'keluar')
                    ->when($startDate && $endDate, fn ($q) => $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]));
            }])
            ->orderByDesc('transactions_sum_nominal')
            ->get();

        // 3. Prepare category stats & donut chart segments
        $categoryBreakdown = [];
        $donutSegments = [];
        $accumulatedPercentage = 0.0;
        $paletteCount = count($this->palette);

        foreach ($categoriesRaw as $index => $category) {
            $sum = (int) ($category->transactions_sum_nominal ?? 0);
            $count = (int) ($category->transactions_count ?? 0);
            $percentage = $totalExpense > 0 ? round(($sum / $totalExpense) * 100, 1) : 0.0;
            $color = $this->palette[$index % $paletteCount];

            $categoryBreakdown[] = [
                'id' => $category->id,
                'nama' => $category->nama,
                'icon' => $category->icon,
                'sum' => $sum,
                'count' => $count,
                'percentage' => $percentage,
                'color' => $color,
            ];

            // Donut chart stroke-dasharray and stroke-dashoffset (using circumference = 100 with r=15.9155)
            if ($percentage > 0) {
                $donutSegments[] = [
                    'id' => $category->id,
                    'nama' => $category->nama,
                    'color' => $color,
                    'percentage' => $percentage,
                    'dasharray' => sprintf('%.2f %.2f', $percentage, max(0, 100 - $percentage)),
                    // 25 offset rotates starting position from 3 o'clock to 12 o'clock
                    'dashoffset' => sprintf('%.2f', 100 - $accumulatedPercentage + 25),
                ];
                $accumulatedPercentage += $percentage;
            }
        }

        // Active category model if selected
        $activeCategory = $this->selectedCategoryId
            ? $categoriesRaw->firstWhere('id', $this->selectedCategoryId)
            : null;

        // Top spending category
        $topCategory = ! empty($categoryBreakdown) && $categoryBreakdown[0]['sum'] > 0
            ? $categoryBreakdown[0]
            : null;

        // 4. Fetch transactions list
        $transactions = Transaction::query()
            ->where('type', 'keluar')
            ->with(['account', 'category'])
            ->when($startDate && $endDate, fn ($q) => $q->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()]))
            ->when($this->selectedCategoryId, fn ($q) => $q->where('category_id', $this->selectedCategoryId))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.expense-report', [
            'totalExpense' => $totalExpense,
            'totalTransactionsCount' => $totalTransactionsCount,
            'categoryBreakdown' => $categoryBreakdown,
            'donutSegments' => $donutSegments,
            'activeCategory' => $activeCategory,
            'topCategory' => $topCategory,
            'periodLabel' => $this->getPeriodLabel(),
            'transactions' => $transactions,
        ]);
    }
}
