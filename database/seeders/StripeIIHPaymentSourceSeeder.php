<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Seeder;

class StripeIIHPaymentSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PaymentSource::create([
            "title" => "Stripe - IIH Global",
        ]);
    }
}
