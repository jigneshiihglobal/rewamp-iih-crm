<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class AudAddressUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bank = 6 ;

        Bank::where('id',$bank)->update([
            "account_holder"    => "IIH Global Limited",
            "bsb_code"          =>  "774-001",
            "account_number"    => "201608494",
            'bic'               => 'TRWIAUS1XXX',
            "Address"           => "Wise Australia Pty Ltd<br/>Suite 1, Level 11, 66 Goulburn Street<br/>Sydney NSW 2000<br/>Australia"
        ]);
    }
}
