<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Seeder;

class PaypalIntelligent extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $PayPal_Intelligent = 'PayPal - Intelligent IT Hub Pvt. Ltd';
        PaymentSource::create(['title' => $PayPal_Intelligent,'workspace_id' => 1]);
    }
}
