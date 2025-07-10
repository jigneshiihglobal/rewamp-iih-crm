<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSource extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = ["encrypted_id"];

    public function invoice_payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_source_id', 'id');
    }

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }
}
