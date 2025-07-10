<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Seeder;

class PaymentSourceNameChange extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            PaymentSource::where(['title' =>'Stripe - UK','workspace_id' => 1])->update(['title' => 'Stripe - Intelligent IT Hub Limited (UK)']);
        } catch (\Throwable $th) {
        }
    }
}
