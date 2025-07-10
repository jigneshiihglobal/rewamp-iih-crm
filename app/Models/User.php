<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\CausesActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, CausesActivity, SoftDeletes;

    protected $guarded = [];
    protected $appends = [
        "full_name",
        "encrypted_id"
    ];
    protected $dates = ['dob'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'set_password_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getEncryptedIdAttribute()
    {
        return EncryptionHelper::encrypt($this->id);
    }

    public function getTimezoneAttribute()
    {
        return $this->attributes['timezone'] ?: config('app.timezone');
    }

    public function scopeActive($query, $is_active = true)
    {
        $query->where('is_active', $is_active);
    }

    /**
     * Get all of the leads for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to', 'id');
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'user_workspace', 'user_id', 'workspace_id');
    }

    public function active_workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id', 'id');
    }

    public function email_signatures(): HasMany
    {
        return $this->hasMany(EmailSignature::class, 'user_id', 'id');
    }

    public function smtp_credentials(): HasMany
    {
        return $this->hasMany(SmtpCredential::class, 'user_id', 'id');
    }
   
    public function user()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

}
