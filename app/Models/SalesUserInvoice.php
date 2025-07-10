<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class SalesUserInvoice extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = ["encrypted_id"];

    protected $table = 'sales_invoices';

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

      public function user_invoice_items(): HasMany
    {
        return $this->hasMany(SalesUserInvoiceItem::class, 'sales_invoice_id');
    }

     public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function company_detail(): BelongsTo
    {
        return $this->belongsTo(CompanyDetail::class, 'company_detail_id', 'id');
    }

     public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

     public function sales_person(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function generateNextSalesInvoiceNumber()
    {
         $lastInvoice = DB::table('sales_invoices')->where('sales_invoice_number', 'like', 'TMPINV%')
        ->orderBy('id', 'desc')
        ->first();

        if ($lastInvoice && preg_match('/TMPINV(\d+)/', $lastInvoice->sales_invoice_number, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        } else {
            $nextNumber = 1001;
        }

        return 'TMPINV' . $nextNumber;
    }

}
