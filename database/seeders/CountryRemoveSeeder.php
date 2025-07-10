<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryRemoveSeeder extends Seeder
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
            $country = Country::where('id', 227)->where('name', "United States minor outlying islands")->first();
            if (!$country) {
                $this->command->error('Country not found!');
                return;
            }
            $this->command->line('Country detail:');

            $print_arr = [];
            foreach ($country->getAttributes() as $key => $value) {
                $print_arr[] = [$key, $value];
            }
            $this->command->table(['Attribute', 'Value'], $print_arr);

            $delete = $this->command->confirm('Are you sure you want to delete above country permanently?');
            if ($delete) {
                $country->forceDelete();
                $this->command->line('Country permanently deleted successfully!');
                DB::commit();
                return;
            }
            $this->command->error('Operation cancelled!');
        } catch (\Throwable $th) {
            DB::rollback();
            $this->command->line("Error occurred: " . $th->getMessage());
        }
    }
}
