<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\CurrencyHelper;
use App\Models\LiveCurrency;
use Illuminate\Http\Request;

class LiveCurrencyStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'live_currencies:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line("[" . date('Y-m-d H:i:s') . "] Running Command \"" . $this->signature . "\"");


        $orig_max_exec_time = ini_get('max_execution_time');
        ini_set('max_execution_time', 120); // 2 min

        try {
        $endpoint = 'convert';
        $dest_cur = 'GBP';
        $currencies = ['INR','USD','EUR','AUD','CAD'];

        $access_key = config('custom.gbpconvert_access_key');
        $amount = 1;
        $live_base_rate = [];
       foreach($currencies  as $val){

         // initialize CURL:
         $ch = curl_init('http://api.exchangerate.host/'.$endpoint.'?access_key='.$access_key.'&from='.$val.'&to='.$dest_cur.'&amount='.$amount.'');
         //  $ch = curl_init('http://api.exchangerate.host/'.$endpoint.'?access_key='.$access_key.'&from=INR&to='.$dest_cur.'&amount='.$amount.'');
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $json = curl_exec($ch);
         curl_close($ch);
         // Decode JSON response:
         $conversionResult = json_decode($json, true);

         LiveCurrency::where('source_currency',$val)->update(['base_currency_rate'=>$conversionResult['result']]);
         $live_base_rate["GBP".$val] = $conversionResult['result'];
        }

        $currencies = ['INR','USD','EUR','AUD','CAD'];

        $endpoint = 'live';
        $base = 'GBP';
        $access_key = config('custom.gbpconvert_access_key');

        // Initialize CURL:
        $ch = curl_init('http://api.exchangerate.host/'.$endpoint.'?access_key='.$access_key.'&source='.$base.'&currencies='.implode(',',$currencies));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Store the data:
        $json = curl_exec($ch);
        curl_close($ch);

        // Decode JSON response:
        $exchangeRates = json_decode($json, true);

        $inr = LiveCurrency::where('source_currency','INR')->update(['currency_rate'=>$exchangeRates['quotes']['GBPINR']]);

        $usd = LiveCurrency::where('source_currency','USD')->update(["currency_rate"=>$exchangeRates['quotes']['GBPUSD']]);

        $eur = LiveCurrency::where('source_currency','EUR')->update(["currency_rate"=>$exchangeRates['quotes']['GBPEUR']]);

        $aud = LiveCurrency::where('source_currency','AUD')->update(["currency_rate"=>$exchangeRates['quotes']['GBPAUD']]);

        $cad = LiveCurrency::where('source_currency','CAD')->update(["currency_rate"=>$exchangeRates['quotes']['GBPCAD']]);

       $live_rate = [
           "INR" => "INR_currency_rate = ".$exchangeRates['quotes']['GBPINR']." INR_base_currency_rate = ".$live_base_rate["GBPINR"],
           "USD" => "USD_currency_rate = ".$exchangeRates['quotes']['GBPUSD']." USD_base_currency_rate = ".$live_base_rate["GBPUSD"],
           "EUR" => "EUR_currency_rate = ".$exchangeRates['quotes']['GBPEUR']." EUR_base_currency_rate = ".$live_base_rate["GBPEUR"],
           "AUD" => "AUD_currency_rate = ".$exchangeRates['quotes']['GBPAUD']." AUD_base_currency_rate = ".$live_base_rate["GBPAUD"],
           "CAD" => "AUD_currency_rate = ".$exchangeRates['quotes']['GBPCAD']." CAD_base_currency_rate = ".$live_base_rate["GBPCAD"],
       ];

       $this->info("[" . date('Y-m-d H:i:s') . "]. Currency rate updated successfully :" . implode(', ', $live_rate));

        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred live currency not update: " . $th->getMessage());
            $this->error($th);
        } finally {
            ini_set('max_execution_time', $orig_max_exec_time); // revert back to original settings
        }
    }
}
