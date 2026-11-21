<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'dipinjam_orang' (piutang) atau 'kita_pinjam' (hutang)
            $table->string('nama_orang');
            $table->unsignedBigInteger('nominal_total');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('initial_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->date('tanggal');
            $table->date('jatuh_tempo')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('status')->default('belum_lunas'); // 'belum_lunas', 'lunas'
            $table->timestamps();
        });

        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained('debts')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->unsignedBigInteger('nominal');
            $table->date('tanggal');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
    }
};
