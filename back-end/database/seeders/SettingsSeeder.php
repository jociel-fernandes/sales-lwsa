<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('settings')->updateOrInsert(
            ['input' => 'percent_commission'],
            ['value' => env('SALES_PERCENT_COMMISSION', '18'), 'label' => 'Porcentagem de Comissão (0.00%)']
        );
    }
}
