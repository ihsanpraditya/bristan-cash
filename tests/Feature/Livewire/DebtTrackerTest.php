<?php

use App\Livewire\DebtTracker;
use App\Models\Account;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->bca = Account::create([
        'nama' => 'BCA',
        'saldo_awal' => 1000000,
        'warna' => '#16a34a',
        'icon' => 'credit-card',
    ]);
});

it('redirects guests to login page', function () {
    $this->get(route('debts.index'))
        ->assertRedirect(route('login'));
});

it('renders successfully for authenticated user', function () {
    $this->actingAs($this->user);

    $this->get(route('debts.index'))
        ->assertOk()
        ->assertSee('Hutang & Pinjaman')
        ->assertSee('Dipinjam Orang')
        ->assertSee('Hutang Kita');
});

it('can record when someone borrows money from us (dipinjam orang) and deduct account balance', function () {
    Livewire::actingAs($this->user)
        ->test(DebtTracker::class)
        ->call('openCreate', 'dipinjam_orang')
        ->set('nama_orang', 'Mba Dini')
        ->set('nominal', '300.000')
        ->set('account_id', $this->bca->id)
        ->set('tanggal', now()->toDateString())
        ->set('keterangan', 'Pinjam buat bayar uang listrik')
        ->call('saveDebt')
        ->assertHasNoErrors();

    $debt = Debt::where('nama_orang', 'Mba Dini')->first();
    expect($debt)->not->toBeNull()
        ->and($debt->type)->toBe('dipinjam_orang')
        ->and($debt->nominal_total)->toBe(300000)
        ->and($debt->status)->toBe('belum_lunas')
        ->and($debt->initial_transaction_id)->not->toBeNull();

    // Verify transaction exists and account balance reduced
    $transaction = Transaction::find($debt->initial_transaction_id);
    expect($transaction->type)->toBe('keluar')
        ->and($transaction->nominal)->toBe(300000);

    $updatedBca = Account::withCalculatedBalance()->find($this->bca->id);
    expect($updatedBca->balance)->toBe(700000);
});

it('can record when we borrow money from someone (kita pinjam) and increase account balance', function () {
    Livewire::actingAs($this->user)
        ->test(DebtTracker::class)
        ->call('openCreate', 'kita_pinjam')
        ->set('nama_orang', 'Pak RT')
        ->set('nominal', '500.000')
        ->set('account_id', $this->bca->id)
        ->set('tanggal', now()->toDateString())
        ->call('saveDebt')
        ->assertHasNoErrors();

    $debt = Debt::where('nama_orang', 'Pak RT')->first();
    expect($debt)->not->toBeNull()
        ->and($debt->type)->toBe('kita_pinjam')
        ->and($debt->nominal_total)->toBe(500000)
        ->and($debt->status)->toBe('belum_lunas');

    $transaction = Transaction::find($debt->initial_transaction_id);
    expect($transaction->type)->toBe('masuk')
        ->and($transaction->nominal)->toBe(500000);

    $updatedBca = Account::withCalculatedBalance()->find($this->bca->id);
    expect($updatedBca->balance)->toBe(1500000);
});

it('can record installment payment for dipinjam orang and update balance and remaining debt', function () {
    $debt = Debt::create([
        'type' => 'dipinjam_orang',
        'nama_orang' => 'Mas Anto',
        'nominal_total' => 500000,
        'account_id' => $this->bca->id,
        'tanggal' => now()->toDateString(),
        'status' => 'belum_lunas',
    ]);

    // Mas Anto returns 200,000 as first installment into BCA
    Livewire::actingAs($this->user)
        ->test(DebtTracker::class)
        ->call('openPayment', $debt->id)
        ->set('paymentNominal', '200.000')
        ->set('paymentAccountId', $this->bca->id)
        ->set('paymentTanggal', now()->toDateString())
        ->set('paymentCatatan', 'Cicilan pertama')
        ->call('savePayment')
        ->assertHasNoErrors();

    $debt->refresh();
    expect($debt->total_dibayar)->toBe(200000)
        ->and($debt->sisa_nominal)->toBe(300000)
        ->and($debt->status)->toBe('belum_lunas');

    // Cash transaction created for incoming money
    $payment = DebtPayment::where('debt_id', $debt->id)->first();
    expect($payment)->not->toBeNull()
        ->and($payment->nominal)->toBe(200000);

    $trx = Transaction::find($payment->transaction_id);
    expect($trx->type)->toBe('masuk')
        ->and($trx->nominal)->toBe(200000);
});

it('automatically marks debt as lunas when paid in full', function () {
    $debt = Debt::create([
        'type' => 'kita_pinjam',
        'nama_orang' => 'Koperasi',
        'nominal_total' => 400000,
        'account_id' => $this->bca->id,
        'tanggal' => now()->toDateString(),
        'status' => 'belum_lunas',
    ]);

    Livewire::actingAs($this->user)
        ->test(DebtTracker::class)
        ->call('openPayment', $debt->id)
        ->set('paymentNominal', '400.000')
        ->set('paymentAccountId', $this->bca->id)
        ->set('paymentTanggal', now()->toDateString())
        ->call('savePayment')
        ->assertHasNoErrors();

    $debt->refresh();
    expect($debt->total_dibayar)->toBe(400000)
        ->and($debt->sisa_nominal)->toBe(0)
        ->and($debt->status)->toBe('lunas');
});

it('prevents paying more than remaining debt', function () {
    $debt = Debt::create([
        'type' => 'dipinjam_orang',
        'nama_orang' => 'Teman Kantor',
        'nominal_total' => 100000,
        'tanggal' => now()->toDateString(),
        'status' => 'belum_lunas',
    ]);

    Livewire::actingAs($this->user)
        ->test(DebtTracker::class)
        ->call('openPayment', $debt->id)
        ->set('paymentNominal', '150.000')
        ->set('paymentAccountId', $this->bca->id)
        ->set('paymentTanggal', now()->toDateString())
        ->call('savePayment')
        ->assertHasErrors(['paymentNominal']);
});

it('can delete a payment installment and restore remaining amount', function () {
    $debt = Debt::create([
        'type' => 'dipinjam_orang',
        'nama_orang' => 'Sdr Budi',
        'nominal_total' => 300000,
        'tanggal' => now()->toDateString(),
        'status' => 'belum_lunas',
    ]);

    $trx = Transaction::create([
        'type' => 'masuk',
        'account_id' => $this->bca->id,
        'nominal' => 100000,
        'tanggal' => now()->toDateString(),
    ]);

    $payment = DebtPayment::create([
        'debt_id' => $debt->id,
        'account_id' => $this->bca->id,
        'transaction_id' => $trx->id,
        'nominal' => 100000,
        'tanggal' => now()->toDateString(),
    ]);

    expect($debt->sisa_nominal)->toBe(200000);

    Livewire::actingAs($this->user)
        ->test(DebtTracker::class)
        ->call('deletePayment', $payment->id);

    $debt->refresh();
    expect($debt->sisa_nominal)->toBe(300000)
        ->and(Transaction::find($trx->id))->toBeNull();
});

it('can delete debt and cleans up associated transactions', function () {
    $initialTrx = Transaction::create([
        'type' => 'keluar',
        'account_id' => $this->bca->id,
        'nominal' => 250000,
        'tanggal' => now()->toDateString(),
    ]);

    $debt = Debt::create([
        'type' => 'dipinjam_orang',
        'nama_orang' => 'Hapus Saya',
        'nominal_total' => 250000,
        'account_id' => $this->bca->id,
        'initial_transaction_id' => $initialTrx->id,
        'tanggal' => now()->toDateString(),
        'status' => 'belum_lunas',
    ]);

    Livewire::actingAs($this->user)
        ->test(DebtTracker::class)
        ->call('confirmDelete', $debt->id)
        ->call('deleteDebt');

    expect(Debt::find($debt->id))->toBeNull()
        ->and(Transaction::find($initialTrx->id))->toBeNull();
});
