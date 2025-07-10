<?php

namespace App\Models;

use App\Enums\InvoiceType;
use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;
    protected  $dates = ['due_date', 'invoice_date', 'fully_paid_at'];
    protected $guarded = [];
    protected $appends = ["encrypted_id"];

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function invoice_items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id')->withTrashed();
    }

    public function sales_person(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function original_subscription_invoice()
    {
        return $this->belongsTo(Invoice::class, 'original_subscription_invoice_id', 'id');
    }

    public function parent_invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id')->where('type', InvoiceType::INVOICE);
    }

    public function credit_note(): HasOne
    {
        return $this->hasOne(Invoice::class, 'parent_invoice_id')->where('type', InvoiceType::CREDIT_NOTE);
    }

    public function company_detail(): BelongsTo
    {
        return $this->belongsTo(CompanyDetail::class, 'company_detail_id', 'id');
    }

    public function bank_detail(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_detail_id', 'id');
    }

    public function invoice_notes(): HasMany
    {
        return $this->hasMany(InvoicesNote::class, 'invoice_id', 'id');
    }
}
