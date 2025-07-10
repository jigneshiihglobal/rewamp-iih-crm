<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LeadStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();
        $lead_statuses = array(
            array('id' => '1', 'title' => 'New', 'call_back_time' => '0', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-blue','priority'=>'900'),
            array('id' => '3', 'title' => 'Lost', 'call_back_time' => '0', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-danger','priority'=>'500'),
            array('id' => '5', 'title' => 'Follow Up', 'call_back_time' => '1', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-primary','priority'=>'100'),
            array('id' => '9', 'title' => 'Not Suitable', 'call_back_time' => '0', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-secondary','priority'=>'200'),
            array('id' => '11', 'title' => 'Contacted', 'call_back_time' => '0', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-info text-dark','priority'=>'800'),
            array('id' => '12', 'title' => 'Estimated', 'call_back_time' => '0', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-orange','priority'=>'700'),
            array('id' => '13', 'title' => 'Won', 'call_back_time' => '0', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-success','priority'=>'600'),
            array('id' => '14', 'title' => 'Hold', 'call_back_time' => '0', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-yellow text-dark','priority'=>'400'),
            array('id' => '15', 'title' => 'Future Follow Up', 'call_back_time' => '0', 'created_at' => $now, 'updated_at' => $now, 'css_class' => 'bg-pink text-light','priority'=>'300')
        );
        // Schema::disableForeignKeyConstraints();
        // LeadStatus::truncate();
        // Schema::enableForeignKeyConstraints();

        LeadStatus::insert($lead_statuses);
    }
}
