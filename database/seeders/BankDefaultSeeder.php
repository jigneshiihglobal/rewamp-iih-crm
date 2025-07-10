<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class BankDefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currency_id = Currency::where('code','GBP')->first();
        
        Bank::where('is_default',true)->update([
            'currency_id' => $currency_id->id,
            'is_default'  => 0,
        ]);
    }
}
