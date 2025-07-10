<?php

namespace Database\Seeders;

use App\Models\LeadStatus;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriorityLeadStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            LeadStatus::where('id', '1')->update(['priority' => '900']);    // New  
            LeadStatus::where('id', "11")->update(['priority' => '800']);   // Contacted  
            LeadStatus::where('id', "12")->update(['priority' => '700']);   // Estimated  
            LeadStatus::where('id', "13")->update(['priority' => '600']);   // Won  
            LeadStatus::where('id', "3")->update(['priority' => '500']);    // Lost  
            LeadStatus::where('id', "14")->update(['priority' => '400']);   // Hold  
            LeadStatus::where('id', "15")->update(['priority' => '300']);   // Future Follow up  
            LeadStatus::where('id', "9")->update(['priority' => '200']);    // Not Suitable
            LeadStatus::where('id', "5")->update(['priority' => '100']);    // Follow Up
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
        }
    }
}
