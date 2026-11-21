<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Hutang & Pinjaman')]
class DebtTracker extends Component
{
    /**
     * Active tab filter: 'dipinjam_orang' (piutang) or 'kita_pinjam' (hutang).
     */
    public string $activeTab = 'dipinjam_orang';

    /**
     * Status filter: 'belum_lunas' or 'lunas'.
     */
    public string $statusFilter = 'belum_lunas';

    /**
     * Search term for debtor/creditor name.
     */
    public string $search = '';

    /**
     * Modal states.
     */
    public bool $showFormModal = false;

    public bool $showPaymentModal = false;

    public bool $showHistoryModal = false;

    public bool $showDeleteModal = false;

    /**
     * Form state: Create / Edit Debt.
     */
    public bool $isEditing = false;

    public ?int $editingDebtId = null;

    public string $type = 'dipinjam_orang';

    public string $nama_orang = '';

    public string $nominal = '';

    public ?int $account_id = null;

    public string $tanggal = '';

    public ?string $jatuh_tempo = null;

    public ?string $keterangan = null;

    /**
     * Form state: Payment / Settlement.
     */
    public ?int $paymentDebtId = null;

    public string $paymentNominal = '';

    public ?int $paymentAccountId = null;

    public string $paymentTanggal = '';

    public ?string $paymentCatatan = null;

    /**
     * Target debt for deletion or history viewing.
     */
    public ?int $targetDebtId = null;

    /**
     * Mount component.
     */
    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
        $this->paymentTanggal = now()->toDateString();

        $firstAccount = Account::first();
        if ($firstAccount) {
            $this->account_id = $firstAccount->id;
            $this->paymentAccountId = $firstAccount->id;
        }
    }

    /**
     * Open create modal.
     */
    public function openCreate(?string $defaultType = null): void
    {
        $this->resetForm();
        if ($defaultType) {
            $this->type = $defaultType;
        } else {
            $this->type = $this->activeTab;
        }

        $this->tanggal = now()->toDateString();
        $this->showFormModal = true;
    }

    /**
     * Open payment modal.
     */
    public function openPayment(int $debtId): void
    {
        $debt = Debt::with('payments')->findOrFail($debtId);
        $this->paymentDebtId = $debt->id;
        $this->paymentNominal = number_format($debt->sisa_nominal, 0, ',', '.');
        $this->paymentTanggal = now()->toDateString();
        $this->paymentCatatan = null;

        // Default to debt's account or first account
        $this->paymentAccountId = $debt->account_id ?? Account::first()?->id;

        $this->showPaymentModal = true;
    }

    /**
     * Open history modal for payments.
     */
    public function openHistory(int $debtId): void
    {
        $this->targetDebtId = $debtId;
        $this->showHistoryModal = true;
    }

    /**
     * Confirm delete debt.
     */
    public function confirmDelete(int $debtId): void
    {
        $this->targetDebtId = $debtId;
        $this->showDeleteModal = true;
    }

    /**
     * Quick amount buttons for debt creation.
     */
    public function quickAmount(int|string $val): void
    {
        $current = (int) str_replace(['.', ','], '', $this->nominal ?: '0');

        if ($val === '000') {
            $newVal = $current > 0 ? (int) ($current.'000') : 0;
        } else {
            $newVal = $current + (int) $val;
        }

        $this->nominal = $newVal > 0 ? number_format($newVal, 0, ',', '.') : '';
    }

    /**
     * Quick amount buttons for payment.
     */
    public function quickPaymentAmount(int|string $val): void
    {
        $current = (int) str_replace(['.', ','], '', $this->paymentNominal ?: '0');

        if ($val === '000') {
            $newVal = $current > 0 ? (int) ($current.'000') : 0;
        } else {
            $newVal = $current + (int) $val;
        }

        $this->paymentNominal = $newVal > 0 ? number_format($newVal, 0, ',', '.') : '';
    }

    /**
     * Save new or edited Debt.
     */
    public function saveDebt(): void
    {
        $rawNominal = (int) str_replace(['.', ','], '', $this->nominal);

        $rules = [
            'type' => 'required|in:dipinjam_orang,kita_pinjam',
            'nama_orang' => 'required|string|max:100',
            'tanggal' => 'required|date',
            'jatuh_tempo' => 'nullable|date',
            'keterangan' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:accounts,id',
        ];

        if (! $this->isEditing) {
            $rules['nominal'] = 'required';
        }

        $this->validate($rules, [
            'nama_orang.required' => 'Nama orang / pihak harus diisi.',
            'nominal.required' => 'Nominal harus diisi.',
            'tanggal.required' => 'Tanggal harus diisi.',
        ]);

        if (! $this->isEditing && $rawNominal <= 0) {
            $this->addError('nominal', 'Nominal harus lebih dari 0.');

            return;
        }

        DB::transaction(function () use ($rawNominal) {
            if ($this->isEditing && $this->editingDebtId) {
                $debt = Debt::findOrFail($this->editingDebtId);
                $debt->update([
                    'type' => $this->type,
                    'nama_orang' => $this->nama_orang,
                    'tanggal' => $this->tanggal,
                    'jatuh_tempo' => $this->jatuh_tempo ?: null,
                    'keterangan' => $this->keterangan,
                ]);

                // Sync initial transaction note if exists
                if ($debt->initial_transaction_id) {
                    $note = $debt->type === 'dipinjam_orang'
                        ? 'Pinjamkan uang ke '.$debt->nama_orang
                        : 'Pinjaman dari '.$debt->nama_orang;

                    $debt->initialTransaction?->update([
                        'tanggal' => $debt->tanggal,
                        'keterangan' => $debt->keterangan ? $note.' ('.$debt->keterangan.')' : $note,
                    ]);
                }

                session()->flash('toast', 'Catatan berhasil diperbarui! ✨');
            } else {
                $initialTransactionId = null;

                // Create initial cash transaction if account is selected
                if ($this->account_id) {
                    $trxType = $this->type === 'dipinjam_orang' ? 'keluar' : 'masuk';
                    $defaultNote = $this->type === 'dipinjam_orang'
                        ? 'Pinjamkan uang ke '.$this->nama_orang
                        : 'Pinjaman dari '.$this->nama_orang;

                    $trx = Transaction::create([
                        'type' => $trxType,
                        'account_id' => $this->account_id,
                        'category_id' => null,
                        'nominal' => $rawNominal,
                        'keterangan' => $this->keterangan ? $defaultNote.' ('.$this->keterangan.')' : $defaultNote,
                        'tanggal' => $this->tanggal,
                    ]);

                    $initialTransactionId = $trx->id;
                }

                Debt::create([
                    'type' => $this->type,
                    'nama_orang' => $this->nama_orang,
                    'nominal_total' => $rawNominal,
                    'account_id' => $this->account_id,
                    'initial_transaction_id' => $initialTransactionId,
                    'tanggal' => $this->tanggal,
                    'jatuh_tempo' => $this->jatuh_tempo ?: null,
                    'keterangan' => $this->keterangan,
                    'status' => 'belum_lunas',
                ]);

                session()->flash('toast', 'Catatan baru berhasil disimpan! 🎉');
            }
        });

        $this->showFormModal = false;
        $this->resetForm();
    }

    /**
     * Save payment / settlement.
     */
    public function savePayment(): void
    {
        $rawNominal = (int) str_replace(['.', ','], '', $this->paymentNominal);

        $this->validate([
            'paymentDebtId' => 'required|exists:debts,id',
            'paymentAccountId' => 'required|exists:accounts,id',
            'paymentTanggal' => 'required|date',
            'paymentCatatan' => 'nullable|string|max:255',
        ]);

        $debt = Debt::with('payments')->findOrFail($this->paymentDebtId);

        if ($rawNominal <= 0) {
            $this->addError('paymentNominal', 'Nominal bayar harus lebih dari 0.');

            return;
        }

        if ($rawNominal > $debt->sisa_nominal) {
            $this->addError('paymentNominal', 'Nominal bayar melebihi sisa hutang (Rp '.number_format($debt->sisa_nominal, 0, ',', '.').').');

            return;
        }

        DB::transaction(function () use ($debt, $rawNominal) {
            // Cash movement:
            // If dipinjam_orang (piutang): money returns IN (masuk)
            // If kita_pinjam (hutang): money goes OUT (keluar)
            $trxType = $debt->type === 'dipinjam_orang' ? 'masuk' : 'keluar';
            $defaultNote = $debt->type === 'dipinjam_orang'
                ? 'Uang balik dari '.$debt->nama_orang
                : 'Bayar pinjaman ke '.$debt->nama_orang;

            $trx = Transaction::create([
                'type' => $trxType,
                'account_id' => $this->paymentAccountId,
                'category_id' => null,
                'nominal' => $rawNominal,
                'keterangan' => $this->paymentCatatan ? $defaultNote.' ('.$this->paymentCatatan.')' : $defaultNote,
                'tanggal' => $this->paymentTanggal,
            ]);

            DebtPayment::create([
                'debt_id' => $debt->id,
                'account_id' => $this->paymentAccountId,
                'transaction_id' => $trx->id,
                'nominal' => $rawNominal,
                'tanggal' => $this->paymentTanggal,
                'catatan' => $this->paymentCatatan,
            ]);

            $debt->refresh();
            $debt->syncStatus();
        });

        $debt->refresh();
        if ($debt->status === 'lunas') {
            session()->flash('toast', 'Alhamdulillah, catatan ini sudah LUNAS! 🥳🎉');
        } else {
            session()->flash('toast', 'Cicilan berhasil dicatat! Sisa: Rp '.number_format($debt->sisa_nominal, 0, ',', '.').' ✨');
        }

        $this->showPaymentModal = false;
        $this->paymentDebtId = null;
        $this->paymentNominal = '';
        $this->paymentCatatan = null;
    }

    /**
     * Delete an individual payment installment.
     */
    public function deletePayment(int $paymentId): void
    {
        DB::transaction(function () use ($paymentId) {
            $payment = DebtPayment::findOrFail($paymentId);
            $debt = $payment->debt;

            // Delete associated cash transaction
            if ($payment->transaction_id) {
                Transaction::where('id', $payment->transaction_id)->delete();
            }

            $payment->delete();

            $debt->refresh();
            $debt->syncStatus();
        });

        session()->flash('toast', 'Riwayat cicilan berhasil dihapus! Sisa disesuaikan kembali.');
    }

    /**
     * Delete entire Debt record and its associated transactions.
     */
    public function deleteDebt(): void
    {
        if (! $this->targetDebtId) {
            return;
        }

        DB::transaction(function () {
            $debt = Debt::with('payments')->findOrFail($this->targetDebtId);

            // Delete payment transactions
            foreach ($debt->payments as $payment) {
                if ($payment->transaction_id) {
                    Transaction::where('id', $payment->transaction_id)->delete();
                }
            }

            // Delete initial transaction
            if ($debt->initial_transaction_id) {
                Transaction::where('id', $debt->initial_transaction_id)->delete();
            }

            $debt->delete();
        });

        $this->showDeleteModal = false;
        $this->targetDebtId = null;
        session()->flash('toast', 'Catatan hutang/pinjaman berhasil dihapus.');
    }

    /**
     * Reset form fields.
     */
    private function resetForm(): void
    {
        $this->isEditing = false;
        $this->editingDebtId = null;
        $this->nama_orang = '';
        $this->nominal = '';
        $this->jatuh_tempo = null;
        $this->keterangan = null;
    }

    /**
     * Render the component view.
     */
    public function render(): View
    {
        // Totals for summary cards
        $allDebts = Debt::with('payments')->get();

        $totalDipinjamOrang = $allDebts
            ->where('type', 'dipinjam_orang')
            ->sum(fn ($d) => $d->sisa_nominal);

        $totalKitaPinjam = $allDebts
            ->where('type', 'kita_pinjam')
            ->sum(fn ($d) => $d->sisa_nominal);

        $countDipinjamBelumLunas = $allDebts
            ->where('type', 'dipinjam_orang')
            ->where('status', 'belum_lunas')
            ->count();

        $countHutangBelumLunas = $allDebts
            ->where('type', 'kita_pinjam')
            ->where('status', 'belum_lunas')
            ->count();

        // Query active list
        $query = Debt::query()
            ->with(['payments.account', 'account'])
            ->where('type', $this->activeTab)
            ->where('status', $this->statusFilter);

        if (! empty($this->search)) {
            $query->where('nama_orang', 'like', '%'.$this->search.'%');
        }

        $debts = $query->orderBy('tanggal', 'desc')->get();
        $accounts = Account::withCalculatedBalance()->get();

        $targetDebt = $this->targetDebtId ? Debt::with(['payments.account', 'account'])->find($this->targetDebtId) : null;
        $paymentDebt = $this->paymentDebtId ? Debt::with('payments')->find($this->paymentDebtId) : null;

        return view('livewire.debt-tracker', [
            'debts' => $debts,
            'accounts' => $accounts,
            'totalDipinjamOrang' => $totalDipinjamOrang,
            'totalKitaPinjam' => $totalKitaPinjam,
            'countDipinjamBelumLunas' => $countDipinjamBelumLunas,
            'countHutangBelumLunas' => $countHutangBelumLunas,
            'targetDebt' => $targetDebt,
            'paymentDebt' => $paymentDebt,
        ]);
    }
}
