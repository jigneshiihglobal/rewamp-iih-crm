<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\LiveCurrency;
use App\Models\MonthLiveCurrency;

class LiveCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*$livecurrencies = array(
            array('source_currency' => 'GBP', 'base_currency' => 'GBP', 'currency_rate' => 1,'base_currency_rate'=>1),
            array('source_currency' => 'INR', 'base_currency' => 'GBP', 'currency_rate' => 100.54,'base_currency_rate'=>0.0099),
            array('source_currency' => 'USD', 'base_currency' => 'GBP', 'currency_rate' => 1.21,'base_currency_rate'=>0.82),
            array('source_currency' => 'EUR', 'base_currency' => 'GBP', 'currency_rate' => 1.15,'base_currency_rate'=>0.87),
            array('source_currency' => 'AUD', 'base_currency' => 'GBP', 'currency_rate' => 1.92,'base_currency_rate'=>0.52),
        );
        LiveCurrency::insert($livecurrencies);*/

        $currencies = ['GBP', 'INR', 'USD', 'EUR', 'AUD','CAD'];
        $Months = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];

        foreach ($Months as $Month) {
            /* GBP To All Currencies Rate Get */
            $ch = curl_init("https://hmrc.matchilling.com/rate/2023/" . $Month . ".json");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);
            $conversionResult = json_decode($json, true);

            $start_date = $conversionResult["period"]["start"];
            $end_date = $conversionResult["period"]["end"] . " " . "23:59:59";

            /* currency wise record store */
            foreach ($currencies as $currency) {
                $currency_exist = in_array($currency, array_keys($conversionResult['rates']));
                if ($currency_exist) {
                    $currency_rate = $conversionResult['rates'][$currency];
                    $base_currency_rate = 1 / $currency_rate;

                    $live_currency = new MonthLiveCurrency;
                    $live_currency->start_date = $start_date;
                    $live_currency->end_date = $end_date;
                    $live_currency->source_currency = $currency;
                    $live_currency->base_currency = "GBP";
                    $live_currency->currency_rate = $currency_rate;
                    $live_currency->base_currency_rate = number_format($base_currency_rate, 4);
                    $live_currency->save();
                }
                if ($currency == 'GBP') {
                    $live_currency = new MonthLiveCurrency;
                    $live_currency->start_date = $start_date;
                    $live_currency->end_date = $end_date;
                    $live_currency->source_currency = "GBP";
                    $live_currency->base_currency = "GBP";
                    $live_currency->currency_rate = "1";
                    $live_currency->base_currency_rate = "1";
                    $live_currency->save();
                }
            }
        }
    }
}
