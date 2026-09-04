<?php

namespace App\Enums;

enum PingSource: string
{
    case DriverPhone = 'driver_phone';
    case DispatcherPhone = 'dispatcher_phone';
    case AgentRelay = 'agent_relay';
}
