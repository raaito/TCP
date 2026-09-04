<?php

namespace App\Exceptions\Domain;

use Exception;

final class DriverHasActiveTripException extends Exception
{
    public function __construct()
    {
        parent::__construct('Driver already has an active trip.');
    }

    public function getErrorCode(): string
    {
        return 'driver_has_active_trip';
    }
}