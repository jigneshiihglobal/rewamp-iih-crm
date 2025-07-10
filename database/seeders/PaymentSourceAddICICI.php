<?php

namespace Database\Seeders;

use App\Models\PaymentSource;
use Illuminate\Database\Seeder;

class PaymentSourceAddICICI extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $ICICI_India = 'ICICI-India';
            PaymentSource::create(['title' => $ICICI_India,'workspace_id' => 1]);

        } catch (\Throwable $th) {
        }
    }
}
