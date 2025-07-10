<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesInvoiceAccess extends Model
{
      use HasFactory;
    protected $guarded = [];
    protected $appends = ["encrypted_id"];

    protected $table = 'sales_invoice_access';

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

     public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function sales_person(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_id', 'id');
    }

}
