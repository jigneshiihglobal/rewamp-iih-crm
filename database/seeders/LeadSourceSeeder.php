<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();
        $lead_sources = array(
            array('id' => '1', 'title' => 'Social Media', 'allow_audio' => '0', 'created_at' => $now, 'updated_at' => $now),
            array('id' => '2', 'title' => 'Upwork', 'allow_audio' => '0', 'created_at' => $now, 'updated_at' => $now),
            array('id' => '3', 'title' => 'People Per Hour', 'allow_audio' => '0', 'created_at' => $now, 'updated_at' => $now),
            array('id' => '4', 'title' => 'Guru', 'allow_audio' => '0', 'created_at' => $now, 'updated_at' => $now),
            array('id' => '5', 'title' => 'Web inquiry', 'allow_audio' => '0', 'created_at' => $now, 'updated_at' => $now),
            array('id' => '6', 'title' => 'Referral', 'allow_audio' => '0', 'created_at' => $now, 'updated_at' => $now),
            array('id' => '7', 'title' => 'Personal Contact', 'allow_audio' => '0', 'created_at' => $now, 'updated_at' => $now),
            array('id' => '8', 'title' => 'Recurring Business', 'allow_audio' => '0', 'created_at' => $now, 'updated_at' => $now)
        );

        LeadSource::insert($lead_sources);
    }
}
