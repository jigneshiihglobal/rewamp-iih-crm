<?php

namespace Database\Seeders;

use App\Models\LeadType;
use Illuminate\Database\Seeder;

class LeadTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();
        $lead_types = array(
            array('id' => '1', 'title' => 'Type1', 'call_audio' => '1', 'created_at' => $now, 'updated_at' => $now),
            array('id' => '2', 'title' => 'Type2', 'call_audio' => '0', 'created_at' => $now, 'updated_at' => $now)
        );

        LeadType::insert($lead_types);
    }
}
