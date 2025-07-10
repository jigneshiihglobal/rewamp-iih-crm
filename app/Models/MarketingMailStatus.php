<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketingMailStatus extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $table = 'marketing_mails_status';
    protected $appends = ["encrypted_id"];

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }
}
