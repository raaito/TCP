<?php

namespace App\Models;

use App\Enums\PingSource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationPing extends Model
{
    use HasUuids;

    protected $fillable = [
        'trip_id',
        'lat',
        'lng',
        'recorded_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'source' => PingSource::class,
            'lat' => 'float',
            'lng' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    public $timestamps = true;

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = null;

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
