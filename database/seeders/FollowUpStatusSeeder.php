<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FollowUpStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $follow_up_leads = DB::transaction(function () {

            /**
             * Removing status id=5 title='Follow Up'
             * 
             * move leads of the removed status to another status: id=15 title='Future Follow Up'
             */
            $follow_up_leads = collect();

            while (Lead::where('lead_status_id', '5')->count() > 0) {

                $leads = Lead::query()
                    ->withTrashed()
                    ->where('lead_status_id', '5')
                    ->orderBy('created_at', 'ASC')
                    ->cursor();

                foreach ($leads as $lead) {
                    $lead->update(['lead_status_id' => '15']); // Future Follow Up
                    $follow_up_leads->push($lead->id);
                }
            }

            LeadStatus::where('id', '5')->delete();

            return $follow_up_leads;
        });

        if ($follow_up_leads->count()) {
            if (Storage::disk('local')->exists('follow_up_leads_ids.json')) {
                $contents = Storage::disk('local')->get('follow_up_leads_ids.json');
                $contentsArr = json_decode($contents);
                $contentsCollection = collect($contentsArr);
                $follow_up_leads = $follow_up_leads->merge($contentsCollection);
                Storage::disk('local')->delete('follow_up_leads_ids.json');
            }
            Storage::disk('local')->put('follow_up_leads_ids.json', $follow_up_leads->toJson());
        }
    }
}
