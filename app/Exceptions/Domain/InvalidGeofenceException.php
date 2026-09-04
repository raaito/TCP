<?php

namespace App\Exceptions\Domain;

use Exception;

final class InvalidGeofenceException extends Exception
{
    public function __construct(string $message = 'Invalid geofence configuration.')
    {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return 'invalid_geofence';
    }
}
