<?php

namespace App\Models;

use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trip extends Model
{
    use HasUuids;

    protected $fillable = [
        'org_id',
        'vehicle_id',
        'driver_id',
        'corridor_id',
        'origin_lat',
        'origin_lng',
        'cargo_type',
        'departure_time',
        'expected_arrival',
        'status',
        'destination_lat',
        'destination_lng',
        'geofence_radius_m',
        'last_ping_at',
        'auto_closed',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => TripStatus::class,
            'destination_lat' => 'float',
            'destination_lng' => 'float',
            'origin_lat' => 'float',
            'origin_lng' => 'float',
            'geofence_radius_m' => 'integer',
            'auto_closed' => 'boolean',
            'departure_time' => 'datetime',
            'expected_arrival' => 'datetime',
            'last_ping_at' => 'datetime',
        ];
    }

    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function org(): BelongsTo
    {
        return $this->belongsTo(ClientOrg::class, 'org_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function corridor(): BelongsTo
    {
        return $this->belongsTo(Corridor::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(DispatcherUser::class, 'created_by');
    }

    public function checkpointEvents(): HasMany
    {
        return $this->hasMany(CheckpointEvent::class);
    }

    public function locationPings(): HasMany
    {
        return $this->hasMany(LocationPing::class);
    }
}
