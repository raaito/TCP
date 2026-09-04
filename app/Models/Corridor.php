<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Corridor extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'origin',
        'destination',
        'waypoints',
    ];

    protected function casts(): array
    {
        return [
            'waypoints' => 'array',
        ];
    }

    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function clientOrgs(): HasMany
    {
        return $this->hasMany(ClientOrg::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
