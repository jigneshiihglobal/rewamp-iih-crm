<?php

namespace App\Models;

use App\Enums\FollowUpType;
use App\Helpers\EncryptionHelper;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lead extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $appends = ["requirement_with_line_breaks", "encrypted_id", "prj_budget_string", "short_requirement_with_line_breaks"];

    public $prjBudgetObj = [
        "0-500"         => ":currency_symbol:0 to :currency_symbol:500",
        "500-2500"      => ":currency_symbol:500 to :currency_symbol:2500",
        "2500-5000"     => ":currency_symbol:2500 to :currency_symbol:5000",
        "5000"          => ":currency_symbol:5000+"
    ];

    public function getRequirementWithLineBreaksAttribute()
    {
        return nl2br($this->requirement ?? '');
    }

    public function getShortRequirementWithLineBreaksAttribute()
    {
        return nl2br(Str::limit($this->requirement ?? '', 400));
    }

    public function getFullNameAttribute()
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function getPrjBudgetStringAttribute()
    {
        $prj_budget = $this->prj_budget ?? '';
        if(!$prj_budget) {
            return '';
        }

        $currency_symbol = $this->currency && $this->currency->symbol ? $this->currency->symbol : '';
        if($currency_symbol) {
            $prj_budget = str_replace(':currency_symbol:', $currency_symbol, $this->prjBudgetObj[$prj_budget]);
        } else {
            $prj_budget = str_replace(':currency_symbol:', '', $this->prjBudgetObj[$prj_budget]);
        }

        return $prj_budget;
    }

    public function lead_status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class);
    }

    public function lead_type(): BelongsTo
    {
        return $this->belongsTo(LeadType::class);
    }

    public function lead_source(): BelongsTo
    {
        return $this->belongsTo(LeadSource::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lead_notes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    public function country_rel(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }
    public function attachments()
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function follow_ups(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'lead_id', 'id');
    }

    public function email_follow_ups(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'lead_id', 'id')->typeEmail();
    }

    public function call_follow_ups(): HasMany
    {
        return $this->hasMany(FollowUp::class, 'lead_id', 'id')->typeCall();
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }
}
