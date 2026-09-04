import { Link, usePage } from '@inertiajs/react'
import TripForm from '../components/TripForm'

function TripCreateForm() {
    return (
        <TripForm
            title="Create Trip"
            submitUrl="/trips"
            method="post"
            submitLabel="Create Trip"
        />
    )
}

function TripList() {
    const { trips } = usePage().props

    const statusColors = {
        created: 'bg-gray-100 text-gray-800',
        in_transit: 'bg-blue-100 text-blue-800',
        delayed: 'bg-red-100 text-red-800',
        arrived: 'bg-green-100 text-green-800',
        cancelled: 'bg-gray-100 text-gray-500',
    }

    return (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-lg font-medium text-gray-900">Active Trips</h2>
            </div>
            {trips.length === 0 ? (
                <div className="px-6 py-8 text-center text-gray-500 text-sm">No active trips</div>
            ) : (
                <div className="divide-y divide-gray-100">
                    {trips.map(trip => (
                        <Link
                            key={trip.id}
                            href={`/trips/${trip.id}`}
                            className="px-6 py-4 flex items-center justify-between hover:bg-gray-50"
                        >
                            <div className="flex-1">
                                <div className="flex items-center gap-2">
                                    <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${statusColors[trip.status] || 'bg-gray-100'}`}>
                                        {trip.status}
                                    </span>
                                    {trip.cargo_type && (
                                        <span className="text-sm text-gray-500">{trip.cargo_type}</span>
                                    )}
                                </div>
                                <div className="mt-1 text-sm text-gray-700">
                                    {trip.vehicle?.plate_number && <span>{trip.vehicle.plate_number} · </span>}
                                    {trip.driver?.name && <span>{trip.driver.name} · </span>}
                                    {trip.corridor?.name && <span>{trip.corridor.name}</span>}
                                </div>
                            </div>
                            <div className="text-xs text-gray-400">
                                {new Date(trip.created_at).toLocaleDateString()}
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </div>
    )
}

export default function Dashboard() {
    return (
        <div className="space-y-8">
            <TripCreateForm />
            <TripList />
        </div>
    )
}
