<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Activitylog\Models\Activity;

class ActivityLogWorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Activity::whereNull('workspace_id')->orWhere('workspace_id', '')->update(['workspace_id' => 1]);
    }
}
