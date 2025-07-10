<?php

namespace Database\Seeders;

use App\Models\Lead;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignLeadsToUser extends Seeder
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
            $leadsToMoveIds = Lead::withTrashed()->where('assigned_to', '9')->pluck('id'); // Neethu george

            if ($leadsToMoveIds->count()) {
                if (Storage::disk('local')->exists('neethu_to_astha_leads.json')) {

                    $contents = Storage::disk('local')->get('neethu_to_astha_leads.json');
                    $contentsArr = json_decode($contents);
                    $contentsCollection = collect($contentsArr);
                    $leadsToMoveIds = $leadsToMoveIds->merge($contentsCollection);

                    Storage::disk('local')->delete('neethu_to_astha_leads.json');
                }
                Storage::disk('local')->put('neethu_to_astha_leads.json', $leadsToMoveIds->toJson());

                if ($leadsToMoveIds->count()) {
                    $updated = Lead::whereIn('id', $leadsToMoveIds->toArray())->update(['assigned_to' => '6', 'updated_at' => DB::raw('updated_at')]);
                }
                $this->command->line("Leads moved successfully!");
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $this->command->error('Error: ');
            $this->command->error($th->getMessage());
            throw $th;
        }
    }
}
