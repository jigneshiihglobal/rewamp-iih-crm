<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserWorkspace;
use Illuminate\Database\Seeder;

class UserWorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::withTrashed()->get(['id']);

        $users->map(function ($user) {
            $user->workspaces()->sync([1 => [
                'created_at' => now(),
                'updated_at' => now()
            ]]);
        });

        $user = $users->firstWhere('id', 1);

        if($user) {
            $user->workspaces()->attach([2 => [
                'created_at' => now(),
                'updated_at' => now()
            ]]);
        }
    }
}
