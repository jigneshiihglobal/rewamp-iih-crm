<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeedbackToken extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $table = "feedback_tokens";
    protected $appends = ["encrypted_id"];
}
