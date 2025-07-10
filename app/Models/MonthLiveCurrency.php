<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthLiveCurrency extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "month_live_currency";

    protected $dates = ['deleted_at'];
}
