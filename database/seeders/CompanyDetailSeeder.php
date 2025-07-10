<?php

namespace Database\Seeders;

use App\Models\CompanyDetail;
use Illuminate\Database\Seeder;

class CompanyDetailSeeder extends Seeder
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
                "name" => "IIH Global Limited",
                "address"  => "Regus, Cardinal Point,<br/>Park Road, Rickmansworth<br/>WD3 1RE",
                "vat_number" => "443 7847 65",
                "workspace_id" => 1,  // IIH Global Workspace
            ],
            [
                "name" => "Intelligent IT Hub Limited",
                "address"  => "Regus, Cardinal Point,<br/>Park Road, Rickmansworth<br/>WD3 1RE",
                "vat_number" => "278698131",
                "workspace_id" => 1,  // IIH Global Workspace
            ],
            [
                "name" => "Intelligent IT Hub Private Limited",
                "address"  => "C-503A Ganesh Meridian,<br/>Nr. Gujarat High Court,<br/>S. G. Highway,<br/>Ahmedabad-380060,<br/>Gujarat, India",
                "vat_number" => NULL,
                "workspace_id" => 1,  // IIH Global Workspace
            ],
        ];

        foreach ($companieDetails as $companyDetail) {
            CompanyDetail::create($companyDetail);
        }
    }
}
