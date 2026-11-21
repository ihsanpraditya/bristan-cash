<?php

namespace App\Livewire;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Dompet & Rekening')]
class AccountManager extends Component
{
    /**
     * Modal visibility state for creating / editing account.
     */
    public bool $showModal = false;

    /**
     * Modal visibility state for confirming deletion.
     */
    public bool $showDeleteModal = false;

    /**
     * Whether the modal is currently in edit mode.
     */
    public bool $isEditing = false;

    /**
     * Active account ID being edited.
     */
    public ?int $editingAccountId = null;

    /**
     * Active account ID targeted for deletion.
     */
    public ?int $targetAccountId = null;

    /**
     * Error message explaining why an account cannot be deleted.
     */
    public ?string $deleteErrorMessage = null;

    /**
     * Account name input.
     */
    public string $nama = '';

    /**
     * Initial balance input (formatted string).
     */
    public string $saldo_awal = '0';

    /**
     * Account color tag (hex code).
     */
    public string $warna = '#16A34A';

    /**
     * Account icon identifier.
     */
    public string $icon = 'wallet';

    /**
     * Preset color options for easy selection.
     *
     * @var array<string, string>
     */
    public array $presetColors = [
        '#0066AE' => 'Biru',
        '#00AED6' => 'Cyan',
        '#16A34A' => 'Hijau',
        '#EE4D2D' => 'Oranye',
        '#003D79' => 'Navy',
        '#8B5CF6' => 'Ungu',
        '#E31B23' => 'Merah',
        '#64748B' => 'Abu',
    ];

    /**
     * Preset icon options for easy selection.
     *
     * @var array<string, string>
     */
    public array $presetIcons = [
        'wallet' => 'Dompet',
        'credit-card' => 'Kartu ATM',
        'device-phone-mobile' => 'E-Wallet / HP',
        'banknotes' => 'Uang Tunai',
        'building-library' => 'Bank / Tabungan',
    ];

    /**
     * Open modal to create a new account.
     */
    public function openCreate(): void
    {
        $this->resetValidation();
        $this->isEditing = false;
        $this->editingAccountId = null;
        $this->nama = '';
        $this->saldo_awal = '0';
        $this->warna = '#16A34A';
        $this->icon = 'wallet';
        $this->showModal = true;
    }

    /**
     * Open modal to edit an existing account.
     */
    public function edit(int $id): void
    {
        $this->resetValidation();
        $account = Account::findOrFail($id);

        $this->isEditing = true;
        $this->editingAccountId = $account->id;
        $this->nama = $account->nama;
        $this->saldo_awal = number_format($account->saldo_awal, 0, ',', '.');
        $this->warna = $account->warna ?? '#16A34A';
        $this->icon = $account->icon ?? 'wallet';
        $this->showModal = true;
    }

    /**
     * Save newly created or updated account.
     */
    public function save(): void
    {
        $cleanedSaldoAwal = (int) preg_replace('/[^0-9]/', '', (string) $this->saldo_awal);

        $rules = [
            'nama' => ['required', 'string', 'max:50'],
            'saldo_awal' => ['required', 'integer', 'min:0'],
            'warna' => ['required', 'string', 'max:20'],
            'icon' => ['required', 'string', 'max:30'],
        ];

        $validator = validator([
            'nama' => $this->nama,
            'saldo_awal' => $cleanedSaldoAwal,
            'warna' => $this->warna,
            'icon' => $this->icon,
        ], $rules, [
            'nama.required' => 'Nama dompet atau rekening wajib diisi ya.',
            'nama.max' => 'Nama jangan terlalu panjang (maksimal 50 huruf).',
            'saldo_awal.required' => 'Saldo awal wajib diisi (bisa diisi 0).',
            'saldo_awal.min' => 'Saldo awal tidak boleh minus.',
        ]);

        if ($validator->fails()) {
            $this->setErrorBag($validator->getMessageBag());

            return;
        }

        $validated = $validator->validated();

        if ($this->isEditing && $this->editingAccountId !== null) {
            $account = Account::findOrFail($this->editingAccountId);
            $account->update($validated);
            session()->flash('toast', "Dompet '{$account->nama}' berhasil diperbarui! ✨");
        } else {
            $account = Account::create($validated);
            session()->flash('toast', "Dompet '{$account->nama}' berhasil ditambahkan! 🎉");
        }

        $this->showModal = false;
        $this->reset(['isEditing', 'editingAccountId', 'nama', 'saldo_awal']);
    }

    /**
     * Prompt delete confirmation with safety check.
     */
    public function confirmDelete(int $id): void
    {
        $this->targetAccountId = $id;

        $transactionCount = Transaction::where('account_id', $id)
            ->orWhere('target_account_id', $id)
            ->count();

        if ($transactionCount > 0) {
            $this->deleteErrorMessage = "Dompet/rekening ini masih dipakai pada {$transactionCount} catatan transaksi. Agar catatan keuangan tidak berantakan, dompet ini tidak bisa dihapus langsung.";
        } else {
            $this->deleteErrorMessage = null;
        }

        $this->showDeleteModal = true;
    }

    /**
     * Delete account if safe.
     */
    public function deleteAccount(): void
    {
        if ($this->deleteErrorMessage !== null) {
            $this->showDeleteModal = false;

            return;
        }

        if ($this->targetAccountId !== null) {
            $account = Account::find($this->targetAccountId);
            if ($account !== null) {
                $nama = $account->nama;
                $account->delete();
                session()->flash('toast', "Dompet '{$nama}' berhasil dihapus.");
            }
        }

        $this->showDeleteModal = false;
        $this->targetAccountId = null;
    }

    /**
     * Render the Livewire component.
     */
    public function render(): View
    {
        $accounts = Account::withCalculatedBalance()
            ->withCount(['transactions', 'incomingTransfers'])
            ->get();

        return view('livewire.account-manager', [
            'accounts' => $accounts,
            'totalSaldo' => $accounts->sum('balance'),
        ]);
    }
}
