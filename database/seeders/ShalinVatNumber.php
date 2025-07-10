<?php

namespace Database\Seeders;

use App\Models\CompanyDetail;
use Illuminate\Database\Seeder;

class ShalinVatNumber extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CompanyDetail::where('workspace_id',2)->where('name','Shalin Designs Limited')->update(['vat_number' => '460304528']);
    }
}
