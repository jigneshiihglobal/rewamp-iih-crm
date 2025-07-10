<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $banks = [
            [
                'currency_id'               => 3,
                'account_holder'            => "IIH Global Limited",
                'account_number'            => "9600014230236448",
                'account_type'              => "Checking",
                'ach_wire_routing_number'   => "084009519",
                'wise_address'              => "30 W. 26th Street, Sixth Floor New York NY 10010 United States",
            ],
            [
                'currency_id'               => 4,
                'account_holder'            => "IIH Global Limited",
                'bic'                       => "TRWIBEB1XXX",
                'iban'                      => "BE91 9676 5649 7376",
                'wise_address'              => "Avenue Louise 54, Room S52 Brussels 1050 Belgium",
            ],
            [
                'is_default'                => true,
                'account_holder'            => "IIH GLOBAL LIMITED",
                'account_number'            => "74118068",
                'bank_name'                 => "Lloyds Bank",
                'sort_code'                 => "30-99-50",
            ],
            [
                'account_holder'            => "Intelligent It Hub Ltd",
                'sort_code'                 => "23-14-70",
                'account_number'            => "84093410",
                'iban'                      => "GB90 TRWI 2314 7084 0934 10",
                'bank_address'              => "56 Shoreditch High Street London E1 6JJ United Kingdom"
            ],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }
    }
}
