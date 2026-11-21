<?php

namespace App\Models;

use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'nominal',
        'start_month',
        'end_month',
        'keterangan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nominal' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Resolve the active budget for a category in a specific month ('YYYY-MM').
     * Prioritizes custom single-month budgets over recurring budgets.
     */
    public static function forCategoryAndMonth(int $categoryId, string $yearMonth): ?self
    {
        // 1. Cek anggaran khusus bulan tersebut (start_month == end_month == yearMonth)
        $specific = static::where('category_id', $categoryId)
            ->where('start_month', $yearMonth)
            ->where('end_month', $yearMonth)
            ->first();

        if ($specific !== null) {
            return $specific;
        }

        // 2. Cek anggaran berulang (berlaku mulai start_month <= yearMonth)
        return static::where('category_id', $categoryId)
            ->where('start_month', '<=', $yearMonth)
            ->where(function ($query) use ($yearMonth) {
                $query->whereNull('end_month')
                    ->orWhere('end_month', '>=', $yearMonth);
            })
            ->orderBy('start_month', 'desc')
            ->first();
    }
}
