<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStatus extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = ["encrypted_id", "badge"];

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function getBadgeAttribute()
    {
        $css_class = $this->css_class ?? '';
        $title = $this->title ?? '';

        return "<span class='badge {$css_class} bg-gradient' >{$title}</span>";
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
