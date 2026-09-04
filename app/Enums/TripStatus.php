<?php

namespace App\Enums;

enum TripStatus: string
{
    case Created = 'created';
    case InTransit = 'in_transit';
    case Delayed = 'delayed';
    case Arrived = 'arrived';
    case Cancelled = 'cancelled';
}
