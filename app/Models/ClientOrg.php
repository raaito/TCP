<?php

namespace App\Models;

use App\Enums\OrgType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientOrg extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'type',
        'corridor_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => OrgType::class,
        ];
    }

    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function corridor(): BelongsTo
    {
        return $this->belongsTo(Corridor::class);
    }

    public function dispatcherUsers(): HasMany
    {
        return $this->hasMany(DispatcherUser::class, 'org_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'org_id');
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class, 'org_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class, 'org_id');
    }
}
