<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceTracker extends Component
{
    use WithPagination;

    /**
     * Filter period: 'hari_ini', 'minggu_ini', 'bulan_ini', 'semua'
     */
    public string $period = 'bulan_ini';

    /**
     * Optional filter by account ID.
     */
    public ?int $filterAccountId = null;

    /**
     * Optional filter by category ID.
     */
    public ?int $filterCategoryId = null;

    /**
     * Optional filter by transaction type: 'keluar', 'masuk', 'transfer'.
     */
    public ?string $filterType = null;

    /**
     * Modal visibility state for input / edit.
     */
    public bool $showInputModal = false;

    /**
     * Modal visibility state for delete confirmation.
     */
    public bool $showDeleteModal = false;

    /**
     * Whether the form is editing an existing transaction.
     */
    public bool $isEditing = false;

    /**
     * Target transaction ID when editing or deleting.
     */
    public ?int $targetTransactionId = null;

    /**
     * Form state: Transaction type ('keluar', 'masuk', 'transfer').
     */
    public string $type = 'keluar';

    /**
     * Form state: Transaction amount (formatted with thousand separators).
     */
    public string $nominal = '';

    /**
     * Form state: Source account ID.
     */
    public ?int $account_id = null;

    /**
     * Form state: Target account ID (for transfer).
     */
    public ?int $target_account_id = null;

    /**
     * Form state: Category ID (for keluar / masuk).
     */
    public ?int $category_id = null;

    /**
     * Form state: Date string (Y-m-d).
     */
    public string $tanggal = '';

    /**
     * Form state: Optional note.
     */
    public ?string $keterangan = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
        $this->setDefaultAccount();
    }

    /**
     * Set default account if available.
     */
    protected function setDefaultAccount(): void
    {
        if ($this->account_id === null) {
            $default = Account::first();
            if ($default !== null) {
                $this->account_id = $default->id;
            }
        }
    }

    /**
     * Open input modal for creating a new transaction.
     */
    public function openInput(string $type = 'keluar'): void
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->targetTransactionId = null;
        $this->type = in_array($type, ['keluar', 'masuk', 'transfer']) ? $type : 'keluar';
        $this->nominal = '';
        $this->keterangan = null;
        $this->tanggal = now()->toDateString();
        $this->setDefaultAccount();

        if ($this->type === 'transfer') {
            $this->category_id = null;
            $second = Account::where('id', '!=', $this->account_id)->first();
            $this->target_account_id = $second?->id;
        } else {
            $this->target_account_id = null;
            $firstCategory = Category::where('tipe', $this->type)->orderBy('urutan')->first();
            $this->category_id = $firstCategory?->id;
        }

        $this->showInputModal = true;
    }

    /**
     * Change transaction type in the form.
     */
    public function setType(string $type): void
    {
        $this->type = in_array($type, ['keluar', 'masuk', 'transfer']) ? $type : 'keluar';

        if ($this->type === 'transfer') {
            $this->category_id = null;
            if ($this->target_account_id === null || $this->target_account_id === $this->account_id) {
                $second = Account::where('id', '!=', $this->account_id)->first();
                $this->target_account_id = $second?->id;
            }
        } else {
            $this->target_account_id = null;
            $firstCategory = Category::where('tipe', $this->type)->orderBy('urutan')->first();
            $this->category_id = $firstCategory?->id;
        }
    }

    /**
     * Append quick amount or append '000'.
     */
    public function quickAmount(string|int $value): void
    {
        $current = (int) preg_replace('/[^0-9]/', '', (string) $this->nominal);

        if ($value === '000') {
            if ($current > 0) {
                $new = (int) ($current * 1000);
                $this->nominal = number_format($new, 0, ',', '.');
            }
        } else {
            $new = (int) ($current + (int) $value);
            $this->nominal = number_format($new, 0, ',', '.');
        }
    }

    /**
     * Set nominal directly.
     */
    public function setNominalDirect(int $value): void
    {
        $this->nominal = number_format($value, 0, ',', '.');
    }

    /**
     * Open edit modal for an existing transaction.
     */
    public function editTransaction(int $id): void
    {
        $this->resetValidation();
        $transaction = Transaction::findOrFail($id);

        $this->isEditing = true;
        $this->targetTransactionId = $transaction->id;
        $this->type = $transaction->type;
        $this->account_id = $transaction->account_id;
        $this->target_account_id = $transaction->target_account_id;
        $this->category_id = $transaction->category_id;
        $this->nominal = number_format($transaction->nominal, 0, ',', '.');
        $this->tanggal = $transaction->tanggal->toDateString();
        $this->keterangan = $transaction->keterangan;

        $this->showInputModal = true;
    }

    /**
     * Save newly created or edited transaction.
     */
    public function saveTransaction(): void
    {
        $cleanedNominal = (int) preg_replace('/[^0-9]/', '', (string) $this->nominal);
        $nominalForValidation = $cleanedNominal > 0 ? $cleanedNominal : null;

        $rules = [
            'type' => ['required', 'in:keluar,masuk,transfer'],
            'nominal' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'account_id' => ['required', 'exists:accounts,id'],
        ];

        if ($this->type === 'transfer') {
            $rules['target_account_id'] = ['required', 'exists:accounts,id', 'different:account_id'];
            $this->category_id = null;
        } else {
            $rules['category_id'] = ['required', 'exists:categories,id'];
            $this->target_account_id = null;
        }

        $validator = validator([
            'type' => $this->type,
            'nominal' => $nominalForValidation,
            'tanggal' => $this->tanggal,
            'keterangan' => $this->keterangan,
            'account_id' => $this->account_id,
            'target_account_id' => $this->target_account_id,
            'category_id' => $this->category_id,
        ], $rules, [
            'nominal.min' => 'Nominal harus lebih dari 0 ya.',
            'nominal.required' => 'Nominal jangan lupa diisi ya.',
            'account_id.required' => 'Pilih rekening/dompet sumber.',
            'target_account_id.required' => 'Pilih rekening/dompet tujuan.',
            'target_account_id.different' => 'Rekening tujuan harus beda dari rekening asal.',
            'category_id.required' => 'Pilih salah satu kategori.',
        ]);

        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag());

            return;
        }

        $validated = $validator->validated();

        if ($this->isEditing && $this->targetTransactionId !== null) {
            $transaction = Transaction::findOrFail($this->targetTransactionId);
            $transaction->update($validated);
            session()->flash('toast', 'Catatan berhasil diperbarui! ✨');
        } else {
            Transaction::create($validated);
            session()->flash('toast', 'Catatan berhasil disimpan! 🎉');
        }

        $this->showInputModal = false;
        $this->reset(['isEditing', 'targetTransactionId', 'nominal', 'keterangan']);
    }

    /**
     * Prompt delete confirmation.
     */
    public function confirmDelete(int $id): void
    {
        $this->targetTransactionId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Delete transaction.
     */
    public function deleteTransaction(): void
    {
        if ($this->targetTransactionId !== null) {
            $transaction = Transaction::find($this->targetTransactionId);
            if ($transaction !== null) {
                $transaction->delete();
                session()->flash('toast', 'Catatan berhasil dihapus.');
            }
        }

        $this->showDeleteModal = false;
        $this->targetTransactionId = null;
    }

    /**
     * Sync batch offline transactions sent from IndexedDB.
     *
     * @param  array<int, array<string, mixed>>  $items
     */
    public function syncBatch(array $items): int
    {
        if (empty($items)) {
            return 0;
        }

        $syncedCount = 0;

        DB::transaction(function () use ($items, &$syncedCount) {
            foreach ($items as $item) {
                $cleanedNominal = (int) preg_replace('/[^0-9]/', '', (string) ($item['nominal'] ?? 0));
                if ($cleanedNominal <= 0) {
                    continue;
                }

                $type = in_array($item['type'] ?? '', ['keluar', 'masuk', 'transfer']) ? $item['type'] : 'keluar';

                Transaction::create([
                    'type' => $type,
                    'account_id' => $item['account_id'] ?? Account::first()?->id,
                    'target_account_id' => $type === 'transfer' ? ($item['target_account_id'] ?? null) : null,
                    'category_id' => $type !== 'transfer' ? ($item['category_id'] ?? null) : null,
                    'nominal' => $cleanedNominal,
                    'keterangan' => $item['keterangan'] ?? null,
                    'tanggal' => ! empty($item['tanggal']) ? Carbon::parse($item['tanggal'])->toDateString() : now()->toDateString(),
                ]);

                $syncedCount++;
            }
        });

        session()->flash('toast', "$syncedCount catatan dari HP berhasil disinkronkan ke server! ☁️");

        return $syncedCount;
    }

    /**
     * Stream CSV download of current filtered transactions.
     */
    public function exportCsv(): StreamedResponse
    {
        $transactions = $this->getFilteredQuery()->with(['account', 'targetAccount', 'category'])->get();
        $filename = 'keuangan-keluarga-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Tanggal', 'Tipe', 'Dari Rekening/Dompet', 'Ke Rekening/Dompet', 'Kategori', 'Nominal (Rp)', 'Catatan']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->tanggal->format('d/m/Y'),
                    ucfirst($t->type),
                    $t->account?->nama ?? '-',
                    $t->targetAccount?->nama ?? '-',
                    $t->category?->nama ?? ($t->type === 'transfer' ? 'Pindah Saldo' : '-'),
                    $t->nominal,
                    $t->keterangan ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Build filtered transactions query.
     *
     * @return Builder<Transaction>
     */
    protected function getFilteredQuery(): Builder
    {
        $query = Transaction::query()->orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        if ($this->period === 'hari_ini') {
            $query->whereDate('tanggal', now()->toDateString());
        } elseif ($this->period === 'minggu_ini') {
            $query->whereBetween('tanggal', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($this->period === 'bulan_ini') {
            $query->whereBetween('tanggal', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);
        }

        if ($this->filterAccountId !== null) {
            $query->where(function (Builder $q) {
                $q->where('account_id', $this->filterAccountId)
                    ->orWhere('target_account_id', $this->filterAccountId);
            });
        }

        if ($this->filterCategoryId !== null) {
            $query->where('category_id', $this->filterCategoryId);
        }

        if ($this->filterType !== null) {
            $query->where('type', $this->filterType);
        }

        return $query;
    }

    /**
     * Reset pagination when filters change.
     */
    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAccountId(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    /**
     * Render the Livewire component view.
     */
    public function render(): View
    {
        $accounts = Account::withCalculatedBalance()->get();
        $categories = Category::orderBy('urutan')->get();

        $query = $this->getFilteredQuery();

        $summaryQuery = clone $query;
        $totalMasuk = (clone $summaryQuery)->where('type', 'masuk')->sum('nominal');
        $totalKeluar = (clone $summaryQuery)->where('type', 'keluar')->sum('nominal');

        $transactions = $query->with(['account', 'targetAccount', 'category'])->paginate(30);

        return view('livewire.finance-tracker', [
            'accounts' => $accounts,
            'categories' => $categories,
            'transactions' => $transactions,
            'totalSaldo' => $accounts->sum('balance'),
            'totalMasukPeriode' => $totalMasuk,
            'totalKeluarPeriode' => $totalKeluar,
        ]);
    }
}
