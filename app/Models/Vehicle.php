<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasUuids;

    protected $fillable = [
        'org_id',
        'plate_number',
        'capacity_type',
    ];

    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function org(): BelongsTo
    {
        return $this->belongsTo(ClientOrg::class, 'org_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }
}
