<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveCurrency extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = "live_currencies";

    protected $dates = ['deleted_at'];
}
