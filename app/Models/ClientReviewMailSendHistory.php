<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Helpers\EncryptionHelper;

class ClientReviewMailSendHistory extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = ["encrypted_id"];
    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function client_info(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'id');
    }    

    public function getReviewMailSendDateTimeAttribute($value)
{
    return Carbon::parse($value)->format('d F Y'); // Outputs: 17 March 2025
}

}
