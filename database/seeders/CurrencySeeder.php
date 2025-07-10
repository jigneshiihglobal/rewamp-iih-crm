<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = array(
            array('code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'),
            array('code' => 'GBP', 'name' => 'Pound Sterling', 'symbol' => '£'),
            array('code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'),
            array('code' => 'EUR', 'name' => 'Euro', 'symbol' => '€')
        );

        Currency::insert($currencies);
    }
}
