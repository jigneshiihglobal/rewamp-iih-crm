<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = [
        'download_url',
        'encrypted_id'
    ];

    public function fileable()
    {
        return $this->morphTo();
    }

    public function getDownloadUrlAttribute()
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function uploaded_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id', 'id');
    }
}
