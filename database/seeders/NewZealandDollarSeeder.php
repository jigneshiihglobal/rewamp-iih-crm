<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class NewZealandDollarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          $currencies = array(
            array('code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => 'NZ$'),
        );

        Currency::insert($currencies);
    }
}
