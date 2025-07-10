<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (SystemSetting::count()) {
            $this->command->error("Settings already exists! Operation cancelled!");
        } else {
            SystemSetting::create(config('custom.system_settings.default', []));
            $this->command->info("System Settings added successfully!");
        }
    }
}
