<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Lead;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserCountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {

            $countries = Country::select('id', DB::raw('TRIM(LOWER(name)) AS name'))->orderBy('name')->get();
            $leads = Lead::query()
                ->selectRaw('DISTINCT(TRIM(leads.country)) AS country')
                ->whereNotNull('leads.country')
                ->where('leads.country', '!=', '')
                ->whereNull('leads.country_id')
                ->orderBy('country')
                ->get();

            $case_when_stmt_collection = collect();

            $leads->each(function ($lead, $key) use ($countries, &$case_when_stmt_collection) {
                $country = $countries->first(function ($country, $key) use ($lead, &$case_when_stmt_collection) {

                    $country2Arr = preg_split('/[-_\ \.,]/', $country->name);
                    $country2 = implode('', $country2Arr);

                    $lead_country_arr = preg_split('/[-_\ \.,]/', $lead->country);
                    $lead_country = implode('', $lead_country_arr);

                    return ($country->name === $lead->country)
                        || ($country2 === $lead_country)
                        || (stripos($lead_country, 'barcelona') !== false && $country->name === 'spain')
                        || (stripos($lead_country, 'canada') !== false && $country->name === 'canada')
                        || (stripos($lead_country, 'dubai') !== false && $country->name === 'united arab emirates')
                        || (stripos($lead_country, 'Ireland') !== false && $country->name === 'ireland')
                        || (stripos($lead_country, ' IRELAND') !== false && $country->name === 'ireland')
                        || (stripos($lead_country, 'isreal') !== false && $country->name === 'israel')
                        || (stripos($lead_country, 'london') !== false && $country->name === 'united kingdom')
                        || (stripos($lead_country, 'netherland') !== false && $country->name === 'netherlands')
                        || (stripos($lead_country, 'Nottingham') !== false && $country->name === 'united kingdom')
                        || (stripos($lead_country, 'România') !== false && $country->name === 'romania')
                        || (stripos($lead_country, 'SaudiArabia') !== false && $country->name === 'saudi arabia')
                        || (stripos($lead_country, 'SaudiArebia') !== false && $country->name === 'saudi arabia')
                        || (stripos($lead_country, 'srilanka') !== false && $country2 === 'srilanka')
                        || (stripos($lead_country, 'Swedone') !== false && $country->name === 'sweden')
                        || (stripos($lead_country, 'switzerland') !== false && $country2 === 'switzerland')
                        || (stripos($lead_country, 'UK') !== false && $country->name === 'united kingdom')
                        || (stripos($lead_country, 'USA') !== false && $country->name === 'united states')
                        || (stripos($lead_country, 'WestMidlands') !== false && $country->name === 'united kingdom')
                        || (stripos($lead_country, 'Iran') !== false && $country->name === 'iran (islamic republic of)')
                        || (stripos($lead_country, 'Gautemala') !== false && $country->name === 'guatemala')
                        || (stripos($lead_country, 'Australian') !== false && $country->name === 'australia')
                        || (stripos($lead_country, 'Dallas') !== false && $country->name === 'united states')
                        || (stripos($lead_country, 'Texas') !== false && $country->name === 'united states')
                        || (strtolower($lead->country) === 'au' && $country->name === "australia")
                        || (strtolower($lead->country) === 'uae' && $country->name === "united arab emirates")
                        || (strtolower($lead->country) === 'uk' && $country->name === "united kingdom")
                        || (strtolower($lead->country) === 'us' && $country->name === "united states")
                        || (strtolower($lead->country) === 'usa' && $country->name === "united states")
                        || (strtolower($lead->country) === $country->name);
                });
                if ($country) {
                    $this->command->info($lead->country . " => " . $country->name);
                    $lead_country = $lead->country;
                    $country_id = $country->id;
                    $case_when_stmt_collection->push("WHEN leads.country = '{$lead_country}' THEN '{$country_id}' ");
                } else {
                    $this->command->error($lead->country);
                }
            });

            if ($case_when_stmt_collection->count() > 0) {
                $case_when_stmt = $case_when_stmt_collection->implode(' ');

                if (!$this->command->confirm('Do you wish to continue?')) {
                    $this->command->line("Operation cancelled");
                    return;
                }
                Lead::query()
                    ->where('country', '!=', "")
                    ->whereNotNull('country')
                    ->whereNull('country_id')
                    ->update([
                        'country_id' => DB::raw("(CASE " . $case_when_stmt . " END)")
                    ]);
                $this->command->info("Updated lead countries successfully");
            }
        });
    }
}
