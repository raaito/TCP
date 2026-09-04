<?php

namespace App\Models;

use App\Enums\CheckpointSource;
use App\Enums\DelayReason;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckpointEvent extends Model
{
    use HasUuids;

    protected $fillable = [
        'trip_id',
        'checkpoint_name',
        'reported_at',
        'source',
        'delay_flag',
        'delay_reason',
    ];

    protected function casts(): array
    {
        return [
            'source' => CheckpointSource::class,
            'delay_flag' => 'boolean',
            'delay_reason' => DelayReason::class,
            'reported_at' => 'datetime',
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
