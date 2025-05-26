<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        DB::table('transactions')->insert([
            [
                'user_id' => 1,
                'type' => 'income',
                'category' => 'gaji',
                'amount' => 3876998,
                'description' => 'Dummy transaksi 1',
                'date' => '2025-05-03',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'type' => 'expense',
                'category' => 'makan',
                'amount' => 1500000,
                'description' => 'Dummy transaksi 2',
                'date' => '2025-05-04',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'type' => 'expense',
                'category' => 'transportasi',
                'amount' => 200000,
                'description' => 'Dummy transaksi 3',
                'date' => '2025-05-05',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
