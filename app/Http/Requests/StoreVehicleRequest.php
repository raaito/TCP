<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'plate_number' => [
                'required',
                'string',
                'max:32',
                Rule::unique('vehicles', 'plate_number')->where('org_id', $this->user()->org_id),
            ],
            'capacity_type' => ['nullable', 'string', 'max:255'],
        ];
    }
}