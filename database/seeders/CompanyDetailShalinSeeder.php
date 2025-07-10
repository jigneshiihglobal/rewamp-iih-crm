<?php

namespace Database\Seeders;

use App\Models\CompanyDetail;
use Illuminate\Database\Seeder;

class CompanyDetailShalinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companieDetails = [
            [
                "name" => "Shalin Designs Limited",
                "address"  => "Regus, Cardinal Point,<br/>Park Road,<br/>Rickmansworth WD3 1RE",
                "vat_number" => NULL,
                "workspace_id" => 2,  // Shalin Designs Workspace
            ],
        ];

        foreach ($companieDetails as $companyDetail) {
            CompanyDetail::create($companyDetail);
        }
    }
}
