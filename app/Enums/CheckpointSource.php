<?php

namespace App\Enums;

enum CheckpointSource: string
{
    case Dispatcher = 'dispatcher';
    case WhatsApp = 'whatsapp';
    case UssdRelay = 'ussd_relay';
    case Agent = 'agent';
    case System = 'system';
}
