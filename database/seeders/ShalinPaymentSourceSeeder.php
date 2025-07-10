<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Seeder;

class ShalinPaymentSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shalin_payment_sources = ['Stripe','Starling Bank'];
        foreach ($shalin_payment_sources as $sourceTitle) {
            PaymentSource::create([
                'title'=>  $sourceTitle,
                'workspace_id' => '2',
            ]);
        }
    }
}
