<?php

namespace App\Exceptions\Domain;

use Exception;

final class TripNotFoundException extends Exception
{
    public function __construct(string $tripId)
    {
        parent::__construct("Trip [{$tripId}] not found.");
    }

    public function getErrorCode(): string
    {
        return 'trip_not_found';
    }
}
