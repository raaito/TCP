<?php

namespace App\Http\Controllers;

use App\Models\Corridor;
use App\Models\Vehicle;
use App\Services\Contracts\TripServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(private TripServiceInterface $trips) {}

    public function __invoke(Request $request)
    {
        $orgId = $request->user()->org_id;

        return Inertia::render('Dashboard', [
            'trips' => $this->trips->getRecentTrips($orgId),
            'vehicles' => Vehicle::select('id', 'plate_number')
                ->where('org_id', $orgId)
                ->orderBy('plate_number')
                ->get(),
            'drivers' => $this->trips->getAvailableDrivers($orgId),
            'corridors' => Corridor::select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }
}