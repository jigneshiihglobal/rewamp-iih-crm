<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Seeder;

class PaypalIIHSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $PayPal = 'PayPal - IIH Global';
            PaymentSource::create(['title' => $PayPal,'workspace_id' => 1]);

        } catch (\Throwable $th) {
        }
    }
}
