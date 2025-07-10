<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role_users = array(
            array('user_id' => '1', 'role_id' => '1'),
            array('user_id' => '2', 'role_id' => '2'),
            array('user_id' => '3', 'role_id' => '2'),
            array('user_id' => '4', 'role_id' => '2'),
            array('user_id' => '5', 'role_id' => '2'),
            array('user_id' => '6', 'role_id' => '2'),
            array('user_id' => '7', 'role_id' => '1'),
            array('user_id' => '8', 'role_id' => '2'),
            array('user_id' => '9', 'role_id' => '2'),
            array('user_id' => '10', 'role_id' => '2'),
            array('user_id' => '11', 'role_id' => '2'),
            array('user_id' => '12', 'role_id' => '2'),
            array('user_id' => '13', 'role_id' => '2')
        );

        foreach ($role_users as $role_user) {
            $user = User::find($role_user['user_id']);
            if ($user && $role_user['role_id']) {
                $user->syncRoles($role_user['role_id']);
            }
        }
    }
}
