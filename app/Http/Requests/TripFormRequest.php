<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class TripFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'driver_id' => $this->driver_id ?: null,
            'destination_lat' => self::parseCoordinate($this->destination_lat, 'lat'),
            'destination_lng' => self::parseCoordinate($this->destination_lng, 'lng'),
            'origin_lat' => self::parseCoordinate($this->origin_lat, 'lat'),
            'origin_lng' => self::parseCoordinate($this->origin_lng, 'lng'),
            'geofence_radius_m' => $this->geofence_radius_m === '' ? null : $this->geofence_radius_m,
            'departure_time' => $this->departure_time === '' ? null : $this->departure_time,
            'expected_arrival' => $this->expected_arrival === '' ? null : $this->expected_arrival,
            'cargo_type' => $this->cargo_type === '' ? null : $this->cargo_type,
        ]);
    }

    private static function parseCoordinate(?string $value, string $type): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = strtoupper(trim($value));

        if (preg_match('/^(-?\d+(?:\.\d+)?)\s*([NSEW])?$/i', $value, $m)) {
            $num = (float) $m[1];
            $dir = $m[2] ?? null;

            if ($dir === 'S' || $dir === 'W') {
                $num = -abs($num);
            } elseif ($dir === 'N' || $dir === 'E') {
                $num = abs($num);
            }

            return (string) $num;
        }

        return $value;
    }

    public function rules(): array
    {
        return [
            'vehicle_id'       => ['required', 'string', 'max:255'],
            'driver_id'        => ['nullable', 'string', 'max:255'],
            'corridor_id'      => ['required', 'string', 'max:255'],
            'cargo_type'       => ['nullable', 'string', 'max:255'],
            'departure_time'   => ['nullable', 'date'],
            'expected_arrival' => ['nullable', 'date', 'after:departure_time'],
            'destination_lat'  => ['nullable', 'numeric', 'between:-90,90'],
            'destination_lng'  => ['nullable', 'numeric', 'between:-180,180'],
            'origin_lat'       => ['nullable', 'numeric', 'between:-90,90'],
            'origin_lng'       => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_m' => ['nullable', 'integer', 'min:50', 'max:5000'],
        ];
    }
}
