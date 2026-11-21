<?php

namespace App\Models;

use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'saldo_awal',
        'warna',
        'icon',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'saldo_awal' => 'integer',
        ];
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'target_account_id');
    }

    /**
     * Scope query with calculated balance via subqueries to prevent N+1 queries.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeWithCalculatedBalance($query)
    {
        return $query->select('accounts.*')
            ->selectSub(function ($q) {
                $q->from('transactions')
                    ->whereColumn('transactions.account_id', 'accounts.id')
                    ->where('transactions.type', 'masuk')
                    ->selectRaw('COALESCE(SUM(nominal), 0)');
            }, 'total_masuk')
            ->selectSub(function ($q) {
                $q->from('transactions')
                    ->whereColumn('transactions.account_id', 'accounts.id')
                    ->where('transactions.type', 'keluar')
                    ->selectRaw('COALESCE(SUM(nominal), 0)');
            }, 'total_keluar')
            ->selectSub(function ($q) {
                $q->from('transactions')
                    ->whereColumn('transactions.target_account_id', 'accounts.id')
                    ->where('transactions.type', 'transfer')
                    ->selectRaw('COALESCE(SUM(nominal), 0)');
            }, 'total_transfer_masuk')
            ->selectSub(function ($q) {
                $q->from('transactions')
                    ->whereColumn('transactions.account_id', 'accounts.id')
                    ->where('transactions.type', 'transfer')
                    ->selectRaw('COALESCE(SUM(nominal), 0)');
            }, 'total_transfer_keluar');
    }

    /**
     * Dynamically calculate real balance.
     */
    public function getBalanceAttribute(): int
    {
        if (isset($this->attributes['total_masuk'])) {
            return (int) (
                $this->saldo_awal
                + $this->total_masuk
                - $this->total_keluar
                + $this->total_transfer_masuk
                - $this->total_transfer_keluar
            );
        }

        $masuk = $this->transactions()->where('type', 'masuk')->sum('nominal');
        $keluar = $this->transactions()->where('type', 'keluar')->sum('nominal');
        $transferMasuk = $this->incomingTransfers()->where('type', 'transfer')->sum('nominal');
        $transferKeluar = $this->transactions()->where('type', 'transfer')->sum('nominal');

        return (int) ($this->saldo_awal + $masuk - $keluar + $transferMasuk - $transferKeluar);
    }
}
