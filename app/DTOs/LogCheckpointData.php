<?php

namespace App\DTOs;

use App\Enums\CheckpointSource;
use App\Enums\DelayReason;
use App\Http\Requests\LogCheckpointRequest;

final class LogCheckpointData
{
    public function __construct(
        public readonly string $checkpointName,
        public readonly CheckpointSource $source,
        public readonly bool $delayFlag = false,
        public readonly ?DelayReason $delayReason = null,
    ) {}

    public static function fromRequest(LogCheckpointRequest $request): self
    {
        return new self(
            checkpointName: $request->validated('checkpoint_name'),
            source: CheckpointSource::from($request->validated('source')),
            delayFlag: (bool) $request->validated('delay_flag', false),
            delayReason: $request->validated('delay_reason')
                ? DelayReason::from($request->validated('delay_reason'))
                : null,
        );
    }
}
