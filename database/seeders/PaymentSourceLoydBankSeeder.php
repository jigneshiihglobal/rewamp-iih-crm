<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Seeder;

class PaymentSourceLoydBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentSource::create(['title' => 'Lloyds Bank']);
    }
}
