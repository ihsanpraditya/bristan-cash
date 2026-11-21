<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Pengeluaran
            ['nama' => 'Belanja Dapur', 'tipe' => 'keluar', 'icon' => 'shopping-cart', 'urutan' => 1],
            ['nama' => 'Makan & Jajan', 'tipe' => 'keluar', 'icon' => 'sparkles', 'urutan' => 2],
            ['nama' => 'Anak & Sekolah', 'tipe' => 'keluar', 'icon' => 'academic-cap', 'urutan' => 3],
            ['nama' => 'Tagihan & Listrik', 'tipe' => 'keluar', 'icon' => 'bolt', 'urutan' => 4],
            ['nama' => 'Bensin & Transport', 'tipe' => 'keluar', 'icon' => 'truck', 'urutan' => 5],
            ['nama' => 'Kebutuhan Rumah', 'tipe' => 'keluar', 'icon' => 'home', 'urutan' => 6],
            ['nama' => 'Kesehatan & Obat', 'tipe' => 'keluar', 'icon' => 'heart', 'urutan' => 7],
            ['nama' => 'Lain-lain', 'tipe' => 'keluar', 'icon' => 'ellipsis-horizontal-circle', 'urutan' => 8],

            // Pemasukan
            ['nama' => 'Gaji / Nafkah', 'tipe' => 'masuk', 'icon' => 'banknotes', 'urutan' => 1],
            ['nama' => 'Bonus / THR', 'tipe' => 'masuk', 'icon' => 'gift', 'urutan' => 2],
            ['nama' => 'Pendapatan Lain', 'tipe' => 'masuk', 'icon' => 'arrow-trending-up', 'urutan' => 3],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['nama' => $category['nama'], 'tipe' => $category['tipe']],
                $category
            );
        }
    }
}
