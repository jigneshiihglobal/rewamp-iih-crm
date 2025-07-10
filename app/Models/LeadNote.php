<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadNote extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = ["note_with_line_breaks", "encrypted_id"];
    protected $dates = ['last_edited_at'];

    public function getNoteWithLineBreaksAttribute()
    {
        return $this->note ?? '';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function last_edited_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by_user_id', 'id');
    }

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }
}
