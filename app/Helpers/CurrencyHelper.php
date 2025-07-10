<?php

namespace App\Helpers;

use AmrShawky\LaravelCurrency\Facade\Currency;
use App\Models\LiveCurrency;
use App\Models\MonthLiveCurrency;

class CurrencyHelper
{
    public static function convert($src_cur, $dest_cur,$invoice_date) {
        $data = MonthLiveCurrency::where('start_date','<=',$invoice_date)->where('end_date','>=',$invoice_date)->where('source_currency',$src_cur)->where('base_currency',$dest_cur)->first();
        return $data;
    }
}
