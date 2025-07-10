<?php

namespace Database\Seeders;

use App\Models\EmailSignature;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmailSignatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::query()
            ->select(
                'id',
                'first_name',
                'last_name',
                'phone',
                'email',
            )
            ->whereNull('deleted_at')
            ->where('is_active', '!=', '0')
            ->get();

            $users->each(function ($user) {
                EmailSignature::create([
                    'user_id' => $user->id,
                    'name' => implode(' ', [$user->first_name, $user->last_name]),
                    'mobile_number' => $user->phone ?? NULL,
                    'email' => $user->email,
                    'image_link' => 'https://www.iihglobal.com/schedule-call/',
                ]);
            });
    }
}
