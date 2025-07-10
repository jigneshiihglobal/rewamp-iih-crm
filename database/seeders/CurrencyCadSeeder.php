<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencyCadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cadCur = Currency::create([
            "code" => "CAD",
            "name" => "Canadian Dollar",
            "symbol" => "CA$",
        ]);

        Bank::create([
            "currency_id"        => $cadCur->id,
            "account_holder"     => "Iih Global Limited",
            "account_number"     =>  "200110641284",
            "institution_number" => "621",
            "transit_number"     => "16001",
            "bic"                => "TRWICAW1XXX",
            "address"            => "Wise Payments Canada Inc.<br/>99 Bank Street, Suite 1420<br/>Ottawa<br/>K1P 1H4<br/>Canada"
        ]);
    }
}
