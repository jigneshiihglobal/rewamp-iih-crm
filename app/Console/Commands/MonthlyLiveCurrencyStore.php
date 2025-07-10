<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Models\Currency;
use App\Models\MonthLiveCurrency;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\LiveCurrency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class MonthlyLiveCurrencyStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monthly_live_currencies:monthlyStore';

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

            $currencies = Currency::withTrashed()->pluck('code')->toArray();

            /* GBP To All Currencies Rate Get */
            $ch = curl_init("https://hmrc.matchilling.com/rate/latest.json");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);
            $conversionResult = json_decode($json, true);

            $start_date = $conversionResult["period"]["start"];
            $end_date = $conversionResult["period"]["end"]." "."23:59:59";
            $month_name = Carbon::parse($start_date);
            $monthName = $month_name->format('F');
            $live_rate = [];
            /* currency wise record store */
            foreach ($currencies as $currency){
                $currency_exist = in_array($currency,array_keys($conversionResult['rates']));
                if($currency == 'GBP'){
                    $live_currency = new MonthLiveCurrency;
                    $live_currency->start_date = $start_date;
                    $live_currency->end_date = $end_date;
                    $live_currency->source_currency = "GBP";
                    $live_currency->base_currency = "GBP";
                    $live_currency->currency_rate = "1";
                    $live_currency->base_currency_rate = "1";
                    $live_currency->save();
                }
                if($currency_exist){
                    $currency_rate = $conversionResult['rates'][$currency];
                    $base_currency_rate = 1 / $currency_rate;

                    $live_currency = new MonthLiveCurrency;
                    $live_currency->start_date = $start_date;
                    $live_currency->end_date = $end_date;
                    $live_currency->source_currency = $currency;
                    $live_currency->base_currency = "GBP";
                    $live_currency->currency_rate = $currency_rate;
                    $live_currency->base_currency_rate = number_format($base_currency_rate,4);
                    $live_currency->save();

                    $live_rate += [
                        $currency => $currency."_currency_rate = ".$conversionResult['rates'][$currency]. ' '.$currency."_base_currency_rate = ".number_format($base_currency_rate,4),
                    ];
                }
            }

            $this->info("[" . date('Y-m-d H:i:s') . "]. Currency rate store successfully :" . implode(', ', $live_rate));

            ActivityLogHelper::log(
                'currency.monthly_live_currency',
                '['.$monthName.'] HMRC rate inserted successfully',
                [],
                request(),
                User::role('Superadmin')->first(),
                $live_currency
            );
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred monthly live currency not store: " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        }finally {
            ini_set('max_execution_time', $orig_max_exec_time); // revert back to original settings
        }
    }

    public function sendCronErrorMail(\Throwable $th = null, string $title = '')
    {
        try {
            Mail::to(
                config(
                    'custom.cron_email_recipients.error_mail_address',
                    []
                )
            )
                ->send(new CronErrorMail($title, $th, "Monthly HMRC rate exchange CRON - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
