<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsdDetailChangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banks = [
                'account_holder'            => "IIH Global Limited",
                'account_number'            => "8313363518",
                'account_type'              => "Checking",
                'ach_wire_routing_number'   => "026073150",
                'wise_address'              => "30 W. 26th Street, Sixth Floor New York NY 10010 United States",
        ];

        DB::table('banks')->where('currency_id', 3)->update($banks);
    }
}
