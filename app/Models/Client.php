<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = ["encrypted_id"];
    protected $casts = [
        'is_tree_planted' => 'boolean',
        'plant_a_tree' => 'boolean',
    ];

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function clientReviewMailSendHistory(): HasMany
    {
        return $this->hasMany(ClientReviewMailSendHistory::class, 'client_id', 'id');
    }    

    public function clientFeedbackMail(): HasMany
    {
        return $this->hasMany(ClientFeedback::class, 'client_id', 'id');
    }    

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }

    public function projectList(): HasMany
    {
        return $this->hasMany(CustomerProject::class, 'customer_id', 'id');
    }

    public function salesUserList(): HasMany
    {
        return $this->hasMany(SalesInvoiceAccess::class, 'client_id', 'id');
    }

    public function createdUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
