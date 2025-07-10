<?php

namespace Database\Seeders;

use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $workspace1 = Workspace::create([
            'id' => 1,
            'name' => 'IIH Global',
            'slug' => Str::slug('IIH Global')
        ]);
        $workspace2 = Workspace::create([
            'id' => 2,
            'name' => 'Shalin Designs',
            'slug' => Str::slug('Shalin Designs')
        ]);
    }
}
