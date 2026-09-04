<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class DispatcherUser extends Authenticatable
{
    use HasUuids, Notifiable;

    protected $fillable = [
        'org_id',
        'name',
        'phone_number',
        'role',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function org(): BelongsTo
    {
        return $this->belongsTo(ClientOrg::class, 'org_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'created_by');
    }
}
