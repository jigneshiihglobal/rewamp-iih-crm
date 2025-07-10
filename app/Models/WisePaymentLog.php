<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\EncryptionHelper;

class WisePaymentLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $appends = ['encrypted_id'];

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }
}
