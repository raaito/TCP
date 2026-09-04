# Graph Report - tradecorridor  (2026-08-10)

## Corpus Check
- 137 files · ~36,227 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 573 nodes · 927 edges · 88 communities (86 shown, 2 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 52 edges (avg confidence: 0.8)
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

## God Nodes (most connected - your core abstractions)
1. `Trip` - 69 edges
2. `ClientOrg` - 34 edges
3. `Vehicle` - 28 edges
4. `Corridor` - 26 edges
5. `TripServiceTest` - 26 edges
6. `TripControllerTest` - 21 edges
7. `DispatcherUser` - 18 edges
8. `Driver` - 18 edges
9. `GeofenceServiceTest` - 18 edges
10. `TestCase` - 14 edges

## Surprising Connections (you probably didn't know these)
- `GeofenceServiceTest` --references--> `ClientOrg`  [EXTRACTED]
  tests/Unit/GeofenceServiceTest.php → app/Models/ClientOrg.php
- `TripServiceTest` --references--> `ClientOrg`  [EXTRACTED]
  tests/Unit/TripServiceTest.php → app/Models/ClientOrg.php
- `GeofenceServiceTest` --references--> `Corridor`  [EXTRACTED]
  tests/Unit/GeofenceServiceTest.php → app/Models/Corridor.php
- `TripServiceTest` --references--> `Corridor`  [EXTRACTED]
  tests/Unit/TripServiceTest.php → app/Models/Corridor.php
- `GeofenceServiceTest` --references--> `Vehicle`  [EXTRACTED]
  tests/Unit/GeofenceServiceTest.php → app/Models/Vehicle.php

## Import Cycles
- None detected.

## Communities (88 total, 2 thin omitted)

### Community 0 - "ClientOrg"
Cohesion: 0.06
Nodes (13): TripArrived, TripCreated, TripDelayed, Driver, Trip, createDriver(), TripStatus, TripService (+5 more)

### Community 1 - "TripServiceInterface.php"
Cohesion: 0.08
Nodes (13): LogCheckpointData, self, DriverHasActiveTripException, InvalidGeofenceException, TripNotFoundException, LogCheckpointRequest, CheckpointSource, DatabaseSeeder (+5 more)

### Community 2 - "composer.json"
Cohesion: 0.05
Nodes (41): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+33 more)

### Community 4 - "package.json"
Cohesion: 0.06
Nodes (30): concurrently, @inertiajs/react, laravel-vite-plugin, leaflet, dependencies, @inertiajs/react, leaflet, react (+22 more)

### Community 5 - "GeofenceServiceInterface.php"
Cohesion: 0.14
Nodes (7): CheckStaleTrips, LocationPing, evaluatePing(), markStaleTripsDelayed(), staleTrips(), Illuminate\Console\Command, GeofenceServiceTest

### Community 6 - "DispatcherUser"
Cohesion: 0.16
Nodes (4): CreateTripData, self, CreateTripRequest, createTrip()

### Community 7 - "scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.07
Nodes (14): SessionController, Controller, DashboardController, FleetController, HandleInertiaRequests, StoreCorridorRequest, StoreDriverRequest, StoreVehicleRequest (+6 more)

### Community 9 - "TRADECORRIDOR — MVP Build Plan v0.2"
Cohesion: 0.12
Nodes (15): 1. Positioning (read this before writing any code), 2. Target User (v0.1), 3. Data Model, 4. Core Components (v0.1 scope only), 5. Tech Stack, 6. Build Sequence, 7. What "Proof" Looks Like Before You Expand, 8. The Long-Term Path (how "continent-wide" actually happens) (+7 more)

### Community 10 - "Service-Layer Conventions"
Cohesion: 0.15
Nodes (12): 10. Applying this to the billing/accounting app, 1. Core principle, 2. Folder structure, 3. Why interfaces (`Contracts/`) even for a solo project, 4. Service class shape, 5. DTOs — the shape crossing every boundary, 6. Errors — one shape, always, 7. Controllers — deliberately boring (+4 more)

### Community 11 - "GeofenceServiceTest"
Cohesion: 0.07
Nodes (20): TripController, CheckpointEvent, AppServiceProvider, createCorridor(), createVehicle(), getAllCorridors(), getOrgDrivers(), getOrgVehicles() (+12 more)

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
Cohesion: 0.24
Nodes (4): DEFAULT_CENTER, formatCoord(), MapPicker(), markerIcon

### Community 84 - "DispatcherUser"
Cohesion: 0.06
Nodes (18): ClientOrg, Corridor, DispatcherUser, Vehicle, Illuminate\Database\Eloquent\Concerns\HasUuids, Illuminate\Database\Eloquent\Model, Illuminate\Database\Eloquent\Relations\HasMany, Illuminate\Foundation\Auth\User (+10 more)

## Knowledge Gaps
- **98 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+93 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **2 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Trip` connect `ClientOrg` to `TripServiceInterface.php`, `GeofenceServiceInterface.php`, `DispatcherUser`, `GeofenceServiceTest`, `DispatcherUser`?**
  _High betweenness centrality (0.072) - this node is a cross-community bridge._
- **Why does `ClientOrg` connect `DispatcherUser` to `ClientOrg`, `TripServiceInterface.php`, `GeofenceServiceInterface.php`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Why does `Corridor` connect `DispatcherUser` to `ClientOrg`, `TripServiceInterface.php`, `GeofenceServiceInterface.php`, `DispatcherUser`, `GeofenceServiceTest`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Are the 24 inferred relationships involving `Trip` (e.g. with `.run()` and `.test_active_trips_returns_json()`) actually correct?**
  _`Trip` has 24 INFERRED edges - model-reasoned connections that need verification._
- **Are the 3 inferred relationships involving `Vehicle` (e.g. with `.resolveVehicleId()` and `.__invoke()`) actually correct?**
  _`Vehicle` has 3 INFERRED edges - model-reasoned connections that need verification._
- **Are the 6 inferred relationships involving `Corridor` (e.g. with `.resolveCorridorId()` and `.__invoke()`) actually correct?**
  _`Corridor` has 6 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _98 weakly-connected nodes found - possible documentation gaps or missing edges._