<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, SoftDeletes;
    protected $appends = ["encrypted_id"];

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function banks(): HasMany
    {
        return $this->hasMany(Bank::class, 'currency_id', 'id');
    }
}
