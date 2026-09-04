<?php

namespace App\Http\Controllers;

use App\DTOs\CreateTripData;
use App\DTOs\LogCheckpointData;
use App\DTOs\UpdateTripData;
use App\Enums\TripStatus;
use App\Http\Requests\CreateTripRequest;
use App\Http\Requests\LogCheckpointRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Corridor;
use App\Models\Vehicle;
use App\Services\Contracts\TripServiceInterface;
use Inertia\Inertia;

class TripController extends Controller
{
    public function __construct(private TripServiceInterface $trips) {}

    public function show(string $tripId)
    {
        $trip = $this->trips->getTripDetailForOrg($tripId, request()->user()->org_id);

        return Inertia::render('TripDetail', [
            'trip' => $trip,
        ]);
    }

    public function edit(string $tripId)
    {
        $user = request()->user();

        $trip = $this->trips->getTripForOrg($tripId, $user->org_id);

        return Inertia::render('TripEdit', [
            'trip' => $trip,
            'vehicles' => Vehicle::select('id', 'plate_number')
                ->where('org_id', $user->org_id)
                ->orderBy('plate_number')
                ->get(),
            'drivers' => $this->trips->getDriversForTripEdit($tripId, $user->org_id),
            'corridors' => Corridor::select('id', 'name')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(string $tripId, UpdateTripRequest $request)
    {
        $this->trips->updateTrip($tripId, $request->user()->org_id, UpdateTripData::fromRequest($request));

        return redirect()->route('trips.show', $tripId);
    }

    public function store(CreateTripRequest $request)
    {
        $this->trips->createTrip(CreateTripData::fromRequest($request));

        return redirect()->route('dashboard');
    }

    public function logCheckpoint(string $tripId, LogCheckpointRequest $request)
    {
        $this->trips->getTripForOrg($tripId, $request->user()->org_id);

        $event = $this->trips->logCheckpoint($tripId, LogCheckpointData::fromRequest($request));

        return response()->json(['data' => $event], 201);
    }

    public function activeTrips()
    {
        $trips = $this->trips->getActiveTrips(request()->user()->org_id);

        return response()->json(['data' => $trips]);
    }

    public function closeManually(string $tripId)
    {
        $this->trips->getTripForOrg($tripId, request()->user()->org_id);

        $trip = $this->trips->closeTripManually($tripId, request()->input('reason', 'manual'));

        if (request()->header('X-Inertia')) {
            return redirect()->route('trips.show', $tripId);
        }

        return response()->json(['data' => $trip]);
    }

    public function updateStatus(string $tripId)
    {
        $this->trips->getTripForOrg($tripId, request()->user()->org_id);

        $status = request()->validate(['status' => 'required|string'])['status'];
        $trip = $this->trips->updateTripStatus($tripId, TripStatus::from($status));

        if (request()->header('X-Inertia')) {
            return redirect()->route('trips.show', $tripId);
        }

        return response()->json(['data' => $trip]);
    }
}