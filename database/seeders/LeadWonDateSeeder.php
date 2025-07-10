<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadWonDateSeeder extends Seeder
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

            $leadCount = DB::table('leads')
                ->whereNull('won_at')
                ->count();

            $continue = $this->command->confirm("Won at timestamp of {$leadCount} leads will be updated! Do you want to continue?");

            if (!$continue) {
                DB::rollback();
                $this->command->line("Operation cancelled!");
                return;
            }

            $updatedLeadsCount = DB::table('leads')
                ->whereNull('won_at')
                ->update([
                    'won_at' => DB::raw('DATE(created_at)')
                ]);

            $this->command->line("{$updatedLeadsCount} leads updated successfully!");

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            throw $th;
        }
    }
}
