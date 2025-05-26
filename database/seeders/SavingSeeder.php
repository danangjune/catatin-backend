<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SavingSeeder extends Seeder
{
    public function run()
    {
        DB::table('savings')->insert([
            [
                'user_id' => 1,
                'target_amount' => 1000000,
                'saved_amount' => 400000,
                'month' => '2025-05-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'target_amount' => 1200000,
                'saved_amount' => 900000,
                'month' => '2025-04-01',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
