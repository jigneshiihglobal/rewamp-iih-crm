<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Seeder;

class PaymentSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $payment_sources = [
                "ICICI - UK",
                "Stripe - UK",
                "Stripe - India",
                "Wise",
            ];

            foreach ($payment_sources as $title) {
                PaymentSource::create(['title' => $title]);
            }
        } catch (\Throwable $th) {
        }
    }
}
