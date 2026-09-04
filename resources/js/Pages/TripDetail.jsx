import { Link, router, useForm } from '@inertiajs/react'

const STATUS = {
    created: 'bg-gray-100 text-gray-800',
    in_transit: 'bg-blue-100 text-blue-800',
    delayed: 'bg-red-100 text-red-800',
    arrived: 'bg-green-100 text-green-800',
    cancelled: 'bg-gray-100 text-gray-500',
}

const SOURCE_LABELS = {
    dispatcher: 'Dispatcher',
    whatsapp: 'WhatsApp',
    ussd_relay: 'USSD',
    agent: 'Agent',
    system: 'System',
}

function fmtDate(value) {
    if (!value) return '—'
    return new Date(value).toLocaleString()
}

function fmtCoord(value) {
    if (value === null || value === undefined) return '—'
    return Number(value).toFixed(5)
}

export default function TripDetail({ trip }) {
    const checkpoints = trip.checkpoint_events ?? trip.checkpointEvents ?? []
    const pings = trip.location_pings ?? trip.locationPings ?? []
    const active = ['created', 'in_transit', 'delayed'].includes(trip.status)
    const { data, setData, post, processing } = useForm({ reason: '' })

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <Link href="/dashboard" className="text-sm text-gray-500 hover:text-gray-700">← Back to dashboard</Link>
                <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${STATUS[trip.status] || STATUS.created}`}>
                    {trip.status}
                </span>
            </div>

            {active && (
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-medium text-gray-900">Trip actions</h2>
                        <Link href={`/trips/${trip.id}/edit`} className="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                            Edit trip details
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Status</label>
                            <select
                                value={trip.status}
                                onChange={e => router.post(`/trips/${trip.id}/status`, { status: e.target.value }, {
                                    onError: err => alert('Error: ' + JSON.stringify(err)),
                                })}
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border bg-white"
                            >
                                <option value="in_transit">In transit</option>
                                <option value="delayed">Delayed</option>
                            </select>
                        </div>

                        <form onSubmit={e => { e.preventDefault(); post(`/trips/${trip.id}/close`) }} className="space-y-3">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Close reason (optional)</label>
                                <input type="text" value={data.reason} onChange={e => setData('reason', e.target.value)}
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border" />
                            </div>
                            <button type="submit" disabled={processing}
                                className="px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-900 disabled:opacity-50">
                                {processing ? 'Closing...' : 'Close trip'}
                            </button>
                        </form>
                    </div>
                </div>
            )}

            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex flex-wrap items-center gap-x-6 gap-y-2">
                    <h1 className="text-xl font-semibold text-gray-900">{trip.corridor?.name ?? 'Trip'}</h1>
                    {trip.cargo_type && <span className="text-sm text-gray-500">{trip.cargo_type}</span>}
                    {trip.vehicle?.plate_number && <span className="text-sm text-gray-500">{trip.vehicle.plate_number}</span>}
                    {trip.driver?.name && <span className="text-sm text-gray-500">{trip.driver.name}</span>}
                </div>

                <dl className="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt className="text-gray-500">Departed</dt>
                        <dd className="text-gray-900">{fmtDate(trip.departure_time)}</dd>
                    </div>
                    <div>
                        <dt className="text-gray-500">Expected arrival</dt>
                        <dd className="text-gray-900">{fmtDate(trip.expected_arrival)}</dd>
                    </div>
                    <div>
                        <dt className="text-gray-500">Origin</dt>
                        <dd className="text-gray-900">
                            {fmtCoord(trip.origin_lat)}, {fmtCoord(trip.origin_lng)}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-gray-500">Destination</dt>
                        <dd className="text-gray-900">
                            {fmtCoord(trip.destination_lat)}, {fmtCoord(trip.destination_lng)}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-gray-500">Geofence radius</dt>
                        <dd className="text-gray-900">{trip.geofence_radius_m ?? 200}m</dd>
                    </div>
                    <div>
                        <dt className="text-gray-500">Last ping</dt>
                        <dd className="text-gray-900">{fmtDate(trip.last_ping_at)}</dd>
                    </div>
                    {trip.auto_closed && (
                        <div>
                            <dt className="text-gray-500">Closed by</dt>
                            <dd className="text-gray-900">Geofence (auto)</dd>
                        </div>
                    )}
                </dl>
            </div>

            <div className="bg-white rounded-lg shadow-sm border border-gray-200">
                <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 className="text-lg font-medium text-gray-900">Checkpoint timeline</h2>
                </div>
                {checkpoints.length === 0 ? (
                    <div className="px-6 py-8 text-center text-gray-500 text-sm">No checkpoint events logged yet</div>
                ) : (
                    <ol className="divide-y divide-gray-100">
                        {checkpoints.map((cp, i) => (
                            <li key={cp.id ?? `${cp.trip_id}-${i}`} className="px-6 py-4 flex items-start gap-4">
                                <div className="mt-1.5 w-2 h-2 rounded-full bg-indigo-500 shrink-0" />
                                <div className="flex-1">
                                    <div className="flex items-center gap-2">
                                        <span className="text-sm font-medium text-gray-900">{cp.checkpoint_name}</span>
                                        {cp.delay_flag && (
                                            <span className="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                delayed {cp.delay_reason ? `· ${cp.delay_reason}` : ''}
                                            </span>
                                        )}
                                    </div>
                                    <div className="mt-0.5 text-xs text-gray-500">
                                        {SOURCE_LABELS[cp.source] ?? cp.source}
                                    </div>
                                </div>
                                <div className="text-xs text-gray-400 whitespace-nowrap">
                                    {fmtDate(cp.reported_at ?? cp.created_at)}
                                </div>
                            </li>
                        ))}
                    </ol>
                )}
            </div>

            <div className="bg-white rounded-lg shadow-sm border border-gray-200">
                <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 className="text-lg font-medium text-gray-900">Location pings</h2>
                    <span className="text-xs text-gray-500">{pings.length} recorded</span>
                </div>
                {pings.length === 0 ? (
                    <div className="px-6 py-8 text-center text-gray-500 text-sm">
                        No location pings — this trip likely came through a non-GPS tier (USSD / agent / manual)
                    </div>
                ) : (
                    <div className="divide-y divide-gray-100">
                        {pings.slice(0, 50).map((ping, i) => (
                            <div key={ping.id ?? `${ping.trip_id}-${i}`} className="px-6 py-3 flex items-center justify-between text-sm">
                                <div className="text-gray-900">
                                    {fmtCoord(ping.lat)}, {fmtCoord(ping.lng)}
                                </div>
                                <div className="text-xs text-gray-400 whitespace-nowrap">
                                    {fmtDate(ping.recorded_at)}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    )
}