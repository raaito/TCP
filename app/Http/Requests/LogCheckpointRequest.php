<?php

namespace App\Http\Requests;

use App\Enums\CheckpointSource;
use App\Enums\DelayReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class LogCheckpointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'checkpoint_name' => ['required', 'string', 'max:255'],
            'source'          => ['required', new Enum(CheckpointSource::class)],
            'delay_flag'      => ['nullable', 'boolean'],
            'delay_reason'    => ['nullable', 'required_if:delay_flag,true', new Enum(DelayReason::class)],
        ];
    }
}
