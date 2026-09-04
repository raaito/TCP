import { Link } from '@inertiajs/react'
import TripForm from '../components/TripForm'

function toDatetimeLocal(value) {
    if (!value) return ''
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return ''
    const pad = n => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

export default function TripEdit({ trip }) {
    const initial = {
        vehicle_id: trip.vehicle_id,
        driver_id: trip.driver_id ?? '',
        corridor_id: trip.corridor_id,
        cargo_type: trip.cargo_type ?? '',
        departure_time: toDatetimeLocal(trip.departure_time),
        expected_arrival: toDatetimeLocal(trip.expected_arrival),
        origin_lat: trip.origin_lat ?? '',
        origin_lng: trip.origin_lng ?? '',
        destination_lat: trip.destination_lat ?? '',
        destination_lng: trip.destination_lng ?? '',
        geofence_radius_m: String(trip.geofence_radius_m ?? 200),
    }

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <Link href={`/trips/${trip.id}`} className="text-sm text-gray-500 hover:text-gray-700">← Back to trip</Link>
                <span className="text-sm text-gray-500">{trip.corridor?.name ?? 'Trip'}</span>
            </div>

            <TripForm
                title="Edit Trip"
                initial={initial}
                submitUrl={`/trips/${trip.id}`}
                method="put"
                submitLabel="Save Changes"
            />
        </div>
    )
}
