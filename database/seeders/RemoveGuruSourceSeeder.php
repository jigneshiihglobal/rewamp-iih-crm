<?php

namespace Database\Seeders;

use App\Models\LeadSource;
use Illuminate\Database\Seeder;

class RemoveGuruSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            $sourceGuru = LeadSource::findOrFail('4');
            $sourceGuru->delete();

            $this->command->line("Source \"Guru\" soft deleted successfully! It's leads' source will be empty now!");
        } catch (\Throwable $th) {
            $this->command->error($th->getMessage());
        }
    }
}
