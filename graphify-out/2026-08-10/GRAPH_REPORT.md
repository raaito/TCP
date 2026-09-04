# Graph Report - tradecorridor  (2026-08-10)

## Corpus Check
- 126 files · ~34,139 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 502 nodes · 795 edges · 88 communities (86 shown, 2 thin omitted)
- Extraction: 94% EXTRACTED · 6% INFERRED · 0% AMBIGUOUS · INFERRED: 50 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- ClientOrg
- TripServiceInterface.php
- composer.json
- Trip
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
- Illuminate\Database\Seeder
- DispatcherUser

## God Nodes (most connected - your core abstractions)
1. `Trip` - 69 edges
2. `ClientOrg` - 28 edges
3. `TripServiceTest` - 26 edges
4. `Vehicle` - 23 edges
5. `TripControllerTest` - 21 edges
6. `Corridor` - 19 edges
7. `GeofenceServiceTest` - 18 edges
8. `DispatcherUser` - 15 edges
9. `CheckpointEvent` - 13 edges
10. `Driver` - 13 edges

## Surprising Connections (you probably didn't know these)
- `SessionControllerTest` --references--> `ClientOrg`  [EXTRACTED]
  tests/Feature/SessionControllerTest.php → app/Models/ClientOrg.php
- `GeofenceServiceTest` --references--> `ClientOrg`  [EXTRACTED]
  tests/Unit/GeofenceServiceTest.php → app/Models/ClientOrg.php
- `GeofenceServiceTest` --references--> `Corridor`  [EXTRACTED]
  tests/Unit/GeofenceServiceTest.php → app/Models/Corridor.php
- `TripControllerTest` --references--> `DispatcherUser`  [EXTRACTED]
  tests/Feature/TripControllerTest.php → app/Models/DispatcherUser.php
- `GeofenceServiceTest` --references--> `Vehicle`  [EXTRACTED]
  tests/Unit/GeofenceServiceTest.php → app/Models/Vehicle.php

## Import Cycles
- None detected.

## Communities (88 total, 2 thin omitted)

### Community 0 - "ClientOrg"
Cohesion: 0.06
Nodes (12): ClientOrg, Corridor, Driver, Trip, Vehicle, TradeCorridorSeeder, Illuminate\Database\Eloquent\Relations\BelongsTo, Illuminate\Database\Eloquent\Relations\HasMany (+4 more)

### Community 1 - "TripServiceInterface.php"
Cohesion: 0.11
Nodes (11): LogCheckpointData, self, LogCheckpointRequest, CheckpointSource, DelayReason, Illuminate\Database\Eloquent\Concerns\HasUuids, Illuminate\Database\Eloquent\Model, Illuminate\Foundation\Configuration\Middleware (+3 more)

### Community 2 - "composer.json"
Cohesion: 0.05
Nodes (41): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+33 more)

### Community 3 - "Trip"
Cohesion: 0.36
Nodes (6): TripArrived, TripCreated, TripDelayed, Illuminate\Broadcasting\InteractsWithSockets, Illuminate\Foundation\Events\Dispatchable, Illuminate\Queue\SerializesModels

### Community 4 - "package.json"
Cohesion: 0.07
Nodes (28): concurrently, @inertiajs/react, laravel-vite-plugin, dependencies, @inertiajs/react, react, react-dom, @rolldown/pluginutils (+20 more)

### Community 5 - "GeofenceServiceInterface.php"
Cohesion: 0.11
Nodes (7): CheckStaleTrips, LocationPing, AppServiceProvider, evaluatePing(), Illuminate\Console\Command, Illuminate\Support\ServiceProvider, GeofenceServiceTest

### Community 6 - "DispatcherUser"
Cohesion: 0.15
Nodes (5): CreateTripData, self, CreateTripRequest, createTrip(), Illuminate\Foundation\Http\FormRequest

### Community 7 - "scripts"
Cohesion: 0.08
Nodes (26): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+18 more)

### Community 8 - "Illuminate\Http\Request"
Cohesion: 0.15
Nodes (6): SessionController, Controller, DashboardController, HandleInertiaRequests, Illuminate\Http\Request, Inertia\Middleware

### Community 9 - "TRADECORRIDOR — MVP Build Plan v0.2"
Cohesion: 0.12
Nodes (15): 1. Positioning (read this before writing any code), 2. Target User (v0.1), 3. Data Model, 4. Core Components (v0.1 scope only), 5. Tech Stack, 6. Build Sequence, 7. What "Proof" Looks Like Before You Expand, 8. The Long-Term Path (how "continent-wide" actually happens) (+7 more)

### Community 10 - "Service-Layer Conventions"
Cohesion: 0.15
Nodes (12): 10. Applying this to the billing/accounting app, 1. Core principle, 2. Folder structure, 3. Why interfaces (`Contracts/`) even for a solo project, 4. Service class shape, 5. DTOs — the shape crossing every boundary, 6. Errors — one shape, always, 7. Controllers — deliberately boring (+4 more)

### Community 11 - "GeofenceServiceTest"
Cohesion: 0.08
Nodes (16): TripController, CheckpointEvent, markStaleTripsDelayed(), staleTrips(), closeTripManually(), getActiveTrips(), getAvailableDrivers(), getRecentTrips() (+8 more)

### Community 12 - "TripNotFoundException.php"
Cohesion: 0.20
Nodes (4): DriverHasActiveTripException, InvalidGeofenceException, TripNotFoundException, Exception

### Community 13 - "README.md"
Cohesion: 0.25
Nodes (7): About Laravel, Agentic Development, Code of Conduct, Contributing, Learning Laravel, License, Security Vulnerabilities

### Community 14 - "TripDetail.jsx"
Cohesion: 0.47
Nodes (5): fmtCoord(), fmtDate(), SOURCE_LABELS, STATUS, TripDetail()

### Community 84 - "DispatcherUser"
Cohesion: 0.09
Nodes (9): DispatcherUser, User, UserFactory, Illuminate\Database\Eloquent\Factories\Factory, Illuminate\Database\Eloquent\Factories\HasFactory, Illuminate\Foundation\Auth\User, Illuminate\Notifications\Notifiable, static (+1 more)

## Knowledge Gaps
- **94 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+89 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **2 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Trip` connect `ClientOrg` to `TripServiceInterface.php`, `Trip`, `GeofenceServiceInterface.php`, `DispatcherUser`, `GeofenceServiceTest`?**
  _High betweenness centrality (0.084) - this node is a cross-community bridge._
- **Why does `ClientOrg` connect `ClientOrg` to `TripServiceInterface.php`, `DispatcherUser`, `GeofenceServiceInterface.php`?**
  _High betweenness centrality (0.024) - this node is a cross-community bridge._
- **Why does `TripServiceTest` connect `ClientOrg` to `TripServiceInterface.php`, `GeofenceServiceTest`, `DispatcherUser`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **Are the 24 inferred relationships involving `Trip` (e.g. with `.run()` and `.test_active_trips_returns_json()`) actually correct?**
  _`Trip` has 24 INFERRED edges - model-reasoned connections that need verification._
- **Are the 3 inferred relationships involving `Vehicle` (e.g. with `.resolveVehicleId()` and `.__invoke()`) actually correct?**
  _`Vehicle` has 3 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `name`, `type` to the rest of the system?**
  _94 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `ClientOrg` be split into smaller, more focused modules?**
  _Cohesion score 0.06013986013986014 - nodes in this community are weakly interconnected._