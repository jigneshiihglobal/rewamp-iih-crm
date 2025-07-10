<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class IciciBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bank::create([
            'account_holder'    => "Intelligent IT Hub Limited",
            'bank_name'         => "ICICI",
            'account_number'    => "76271639",
            'sort_code'         => "300143",
        ]);
    }
}
