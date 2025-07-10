<?php

namespace App\Models;

use App\Enums\FollowUpType;
use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $appends = ["encrypted_id"];
    protected $casts = [
        "follow_up_at" => "datetime",
        "send_reminder_at" => "datetime",
        "to" => 'array',
        'bcc' => 'array',
        'sales_person_phone' => 'array',
    ];

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function sales_person()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function scopeTypeEmail($q) {
        return  $q->where('type', FollowUpType::EMAIL);
    }

    public function scopeTypeCall($q) {
        return  $q->where('type', FollowUpType::CALL);
    }

    public function email_signature(): BelongsTo
    {
        return $this->belongsTo(EmailSignature::class, 'email_signature_id', 'id');
    }

    public function smtp_credential(): BelongsTo
    {
        return $this->belongsTo(SmtpCredential::class, 'smtp_credential_id', 'id');
    }
}
