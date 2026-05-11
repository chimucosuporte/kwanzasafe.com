<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('exchange_rates')->insert([
            [
                'currency_from' => 'EUR',
                'currency_to' => 'AOA',
                'rate' => 1140.0000,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'currency_from' => 'BRL',
                'currency_to' => 'AOA',
                'rate' => 170.0000,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}