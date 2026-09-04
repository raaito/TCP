<?php

namespace App\Enums;

enum OrgType: string
{
    case Cooperative = 'cooperative';
    case Distributor = 'distributor';
    case Warehouse = 'warehouse';
}
