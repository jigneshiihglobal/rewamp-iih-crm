<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AudCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $audCur = Currency::create([
                "code" => "AUD",
                "name" => "Australian Dollar",
                "symbol" => "A$",
            ]);

            Bank::create([
                "currency_id"       => $audCur->id,
                "account_holder"    => "IIH Global Limited",
                "bsb_code"          =>  "802-985",
                "account_number"    => "910853908",
                "Address"           => "Wise Australia Pty Ltd<br/>36-38 Gipps Street<br/>Collingwood VIC 3066<br/>Australia"
            ]);
        });
    }
}
