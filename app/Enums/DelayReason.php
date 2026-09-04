<?php

namespace App\Enums;

enum DelayReason: string
{
    case Customs = 'customs';
    case Mechanical = 'mechanical';
    case Security = 'security';
    case Traffic = 'traffic';
    case Other = 'other';
}
