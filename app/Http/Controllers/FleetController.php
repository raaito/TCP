<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCorridorRequest;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\StoreVehicleRequest;
use App\Services\Contracts\FleetServiceInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FleetController extends Controller
{
    public function __construct(private FleetServiceInterface $fleet) {}

    public function index(Request $request)
    {
        $orgId = $request->user()->org_id;

        return Inertia::render('Manage', [
            'drivers' => $this->fleet->getOrgDrivers($orgId),
            'vehicles' => $this->fleet->getOrgVehicles($orgId),
            'corridors' => $this->fleet->getAllCorridors(),
        ]);
    }

    public function storeDriver(StoreDriverRequest $request)
    {
        $this->fleet->createDriver(
            $request->user()->org_id,
            $request->validated('name'),
            $request->validated('phone_number'),
        );

        return redirect()->route('manage');
    }

    public function storeVehicle(StoreVehicleRequest $request)
    {
        $this->fleet->createVehicle(
            $request->user()->org_id,
            $request->validated('plate_number'),
            $request->validated('capacity_type'),
        );

        return redirect()->route('manage');
    }

    public function storeCorridor(StoreCorridorRequest $request)
    {
        $this->fleet->createCorridor(
            $request->validated('name'),
            $request->validated('origin'),
            $request->validated('destination'),
            $request->validated('waypoints', []),
        );

        return redirect()->route('manage');
    }
}