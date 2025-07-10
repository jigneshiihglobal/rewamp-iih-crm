<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call([
        //     RoleSeeder::class,
        // ]);
        // $admin = \App\Models\User::factory()->create([
        //     "name" => "Admin Test",
        //     "email" => "admin@yopmail.com",
        // ]);
        // $admin->assignRole("Admin");
        // $user = \App\Models\User::factory()->create([
        //     "name" => "User Test",
        //     "email" => "user@yopmail.com",
        // ]);
        // $user->assignRole("User");
        // // $this->call([
        // //     LeadTypeSeeder::class,
        // //     LeadSourceSeeder::class,
        // //     LeadStatusSeeder::class,
        // // ]);
        $this->call([
            RoleSeeder::class,
            RoleUserSeeder::class,
            CountrySeeder::class,
            CurrencySeeder::class,
            RenameWebInquiryLeadSourceSeeder::class,
            FollowUpStatusSeeder::class
        ]);
    }
}
