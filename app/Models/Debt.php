<?php

namespace App\Models;

use Database\Factories\DebtFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    /** @use HasFactory<DebtFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'nama_orang',
        'nominal_total',
        'account_id',
        'initial_transaction_id',
        'tanggal',
        'jatuh_tempo',
        'keterangan',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal_total' => 'integer',
            'tanggal' => 'date',
            'jatuh_tempo' => 'date',
        ];
    }

    /**
     * @return HasMany<DebtPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'debt_id');
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * @return BelongsTo<Transaction, $this>
     */
    public function initialTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'initial_transaction_id');
    }

    /**
     * Total amount already paid/returned.
     */
    public function getTotalDibayarAttribute(): int
    {
        return (int) $this->payments()->sum('nominal');
    }

    /**
     * Remaining amount left to pay/receive.
     */
    public function getSisaNominalAttribute(): int
    {
        $sisa = $this->nominal_total - $this->total_dibayar;

        return max(0, (int) $sisa);
    }

    /**
     * Check and update status based on remaining amount.
     */
    public function syncStatus(): void
    {
        $newStatus = $this->sisa_nominal <= 0 ? 'lunas' : 'belum_lunas';
        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }
    }

    /**
     * Scope for unpaid debts.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeBelumLunas(Builder $query): Builder
    {
        return $query->where('status', 'belum_lunas');
    }

    /**
     * Scope for paid debts.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeLunas(Builder $query): Builder
    {
        return $query->where('status', 'lunas');
    }

    /**
     * Scope for debts where others owe us (piutang / dipinjam orang).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeDipinjamOrang(Builder $query): Builder
    {
        return $query->where('type', 'dipinjam_orang');
    }

    /**
     * Scope for debts where we owe others (hutang kita / kita pinjam).
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeKitaPinjam(Builder $query): Builder
    {
        return $query->where('type', 'kita_pinjam');
    }
}
