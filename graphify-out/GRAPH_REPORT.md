# Graph Report - tradecorridor  (2026-08-11)

## Corpus Check
- 143 files · ~37,550 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 605 nodes · 1000 edges · 94 communities (88 shown, 6 thin omitted)
- Extraction: 93% EXTRACTED · 7% INFERRED · 0% AMBIGUOUS · INFERRED: 70 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- ClientOrg
- TripServiceInterface.php
- composer.json
- package.json
- GeofenceServiceInterface.php
- DispatcherUser
- scripts
- Illuminate\Http\Request
- TRADECORRIDOR — MVP Build Plan v0.2
- Service-Layer Conventions
- GeofenceServiceTest
- TripNotFoundException.php
- README.md
- TripDetail.jsx
- app.jsx
- Dashboard.jsx
- errors.md
- DispatcherUser
- Trip
- Vehicle
- ClientOrg
- TripArrived.php
- Illuminate\Database\Eloquent\Relations\HasMany
- Illuminate\Database\Eloquent\Relations\BelongsTo

## God Nodes (most connected - your core abstractions)
1. `Trip` - 81 edges
2. `ClientOrg` - 37 edges
3. `Vehicle` - 31 edges
4. `TripServiceTest` - 31 edges
5. `Corridor` - 27 edges
6. `TripControllerTest` - 26 edges
7. `Driver` - 23 edges
8. `DispatcherUser` - 18 edges
9. `GeofenceServiceTest` - 18 edges
10. `TestCase` - 14 edges

## Surprising Connections (you probably didn't know these)
- `resolveDriverId()` --calls--> `Driver`  [INFERRED]
  app/DTOs/TripReferenceResolver.php → app/Models/Driver.php
- `resolveCorridorId()` --calls--> `Corridor`  [INFERRED]
  app/DTOs/TripReferenceResolver.php → app/Models/Corridor.php
- `FleetControllerTest` --references--> `ClientOrg`  [EXTRACTED]
  tests/Feature/FleetControllerTest.php → app/Models/ClientOrg.php
- `SessionControllerTest` --references--> `ClientOrg`  [EXTRACTED]
  tests/Feature/SessionControllerTest.php → app/Models/ClientOrg.php
- `TripControllerTest` --references--> `ClientOrg`  [EXTRACTED]
  tests/Feature/TripControllerTest.php → app/Models/ClientOrg.php

## Import Cycles
- None detected.

## Communities (94 total, 6 thin omitted)

### Community 1 - "TripServiceInterface.php"
Cohesion: 0.08
Nodes (14): LogCheckpointData, self, DriverHasActiveTripException, InvalidGeofenceException, TripNotFoundException, LogCheckpointRequest, CheckpointSource, DatabaseSeeder (+6 more)

### Community 2 - "composer.json"
Cohesion: 0.05
Nodes (41): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+33 more)

### Community 4 - "package.json"
Cohesion: 0.06
Nodes (30): concurrently, @inertiajs/react, laravel-vite-plugin, leaflet, dependencies, @inertiajs/react, leaflet, react (+22 more)

### Community 5 - "GeofenceServiceInterface.php"
Cohesion: 0.20
Nodes (3): LocationPing, evaluatePing(), GeofenceServiceTest

### Community 6 - "DispatcherUser"
Cohesion: 0.08
Nodes (12): CreateTripData, self, self, UpdateTripData, TripController, CreateTripRequest, UpdateTripRequest, createTrip() (+4 more)

### Community 7 - "scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.07
Nodes (11): SessionController, Controller, FleetController, HandleInertiaRequests, StoreCorridorRequest, StoreDriverRequest, StoreVehicleRequest, TripFormRequest (+3 more)

### Community 9 - "TRADECORRIDOR — MVP Build Plan v0.2"
Cohesion: 0.12
Nodes (15): 1. Positioning (read this before writing any code), 2. Target User (v0.1), 3. Data Model, 4. Core Components (v0.1 scope only), 5. Tech Stack, 6. Build Sequence, 7. What "Proof" Looks Like Before You Expand, 8. The Long-Term Path (how "continent-wide" actually happens) (+7 more)

### Community 10 - "Service-Layer Conventions"
Cohesion: 0.15
Nodes (12): 10. Applying this to the billing/accounting app, 1. Core principle, 2. Folder structure, 3. Why interfaces (`Contracts/`) even for a solo project, 4. Service class shape, 5. DTOs — the shape crossing every boundary, 6. Errors — one shape, always, 7. Controllers — deliberately boring (+4 more)

### Community 11 - "GeofenceServiceTest"
Cohesion: 0.06
Nodes (26): CheckStaleTrips, DashboardController, CheckpointEvent, AppServiceProvider, createCorridor(), createDriver(), createVehicle(), getAllCorridors() (+18 more)

### Community 12 - "TripNotFoundException.php"
Cohesion: 0.47
Nodes (3): UserFactory, Illuminate\Database\Eloquent\Factories\Factory, static

### Community 13 - "README.md"
Cohesion: 0.25
Nodes (7): About Laravel, Agentic Development, Code of Conduct, Contributing, Learning Laravel, License, Security Vulnerabilities

### Community 14 - "TripDetail.jsx"
Cohesion: 0.47
Nodes (5): fmtCoord(), fmtDate(), SOURCE_LABELS, STATUS, TripDetail()

### Community 16 - "Dashboard.jsx"
Cohesion: 0.18
Nodes (7): DEFAULT_CENTER, formatCoord(), MapPicker(), markerIcon, TripForm(), toDatetimeLocal(), TripEdit()

### Community 84 - "DispatcherUser"
Cohesion: 0.07
Nodes (10): DispatcherUser, User, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Foundation\Testing\TestCase, Illuminate\Notifications\Notifiable, FleetControllerTest, SessionControllerTest (+2 more)

### Community 89 - "Vehicle"
Cohesion: 0.17
Nodes (6): resolveCorridorId(), resolveDriverId(), resolveVehicleId(), Vehicle, Illuminate\Database\Eloquent\Concerns\HasUuids, Illuminate\Database\Eloquent\Model

### Community 90 - "ClientOrg"
Cohesion: 0.16
Nodes (5): ClientOrg, Corridor, FleetServiceInterface, GeofenceServiceInterface, TripServiceInterface

### Community 91 - "TripArrived.php"
Cohesion: 0.36
Nodes (6): TripArrived, TripCreated, TripDelayed, Illuminate\Broadcasting\InteractsWithSockets, Illuminate\Foundation\Events\Dispatchable, Illuminate\Queue\SerializesModels

## Knowledge Gaps
- **98 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+93 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **6 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Trip` connect `Trip` to `ClientOrg`, `TripServiceInterface.php`, `GeofenceServiceInterface.php`, `DispatcherUser`, `GeofenceServiceTest`, `Vehicle`, `ClientOrg`, `TripArrived.php`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Illuminate\Database\Eloquent\Relations\BelongsTo`?**
  _High betweenness centrality (0.080) - this node is a cross-community bridge._
- **Why does `ClientOrg` connect `ClientOrg` to `ClientOrg`, `TripServiceInterface.php`, `GeofenceServiceInterface.php`, `DispatcherUser`, `Trip`, `Vehicle`, `Illuminate\Database\Eloquent\Relations\HasMany`, `Illuminate\Database\Eloquent\Relations\BelongsTo`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Why does `Corridor` connect `ClientOrg` to `ClientOrg`, `TripServiceInterface.php`, `GeofenceServiceInterface.php`, `GeofenceServiceTest`, `Trip`, `Vehicle`, `Illuminate\Database\Eloquent\Relations\HasMany`?**
  _High betweenness centrality (0.026) - this node is a cross-community bridge._
- **Are the 34 inferred relationships involving `Trip` (e.g. with `.run()` and `.test_active_trips_returns_json()`) actually correct?**
  _`Trip` has 34 INFERRED edges - model-reasoned connections that need verification._
- **Are the 4 inferred relationships involving `Vehicle` (e.g. with `resolveVehicleId()` and `.__invoke()`) actually correct?**
  _`Vehicle` has 4 INFERRED edges - model-reasoned connections that need verification._
- **Are the 7 inferred relationships involving `Corridor` (e.g. with `resolveCorridorId()` and `.__invoke()`) actually correct?**
  _`Corridor` has 7 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _98 weakly-connected nodes found - possible documentation gaps or missing edges._