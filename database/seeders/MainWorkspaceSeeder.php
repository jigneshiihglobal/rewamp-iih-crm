<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MainWorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            WorkspaceSeeder::class,
            SuperadminRoleSeeder::class,
            SuperadminUserSeeder::class,
            UserWorkspaceSeeder::class,
            LeadWorkspaceSeeder::class,
            ActivityLogWorkspaceSeeder::class
        ]);
    }
}
