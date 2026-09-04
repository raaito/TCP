import { useForm, usePage } from '@inertiajs/react'
import MapPicker from './MapPicker'

export default function TripForm({
    title = 'Trip',
    initial = {},
    submitUrl,
    method = 'post',
    submitLabel = 'Save',
    onSuccess,
}) {
    const { vehicles, drivers, corridors } = usePage().props

    const { data, setData, post, put, processing, errors, reset } = useForm({
        vehicle_id: '',
        driver_id: '',
        corridor_id: '',
        cargo_type: '',
        departure_time: '',
        expected_arrival: '',
        origin_lat: '',
        origin_lng: '',
        destination_lat: '',
        destination_lng: '',
        geofence_radius_m: '200',
        ...initial,
    })

    function submit(e) {
        e.preventDefault()

        const opts = {
            onSuccess: () => {
                if (method === 'post') {
                    reset()
                }
                onSuccess?.()
            },
            onError: (err) => {
                console.error('Form submission failed:', err)
                alert('Error: ' + JSON.stringify(err))
            },
        }

        if (method === 'put') {
            put(submitUrl, opts)
        } else {
            post(submitUrl, opts)
        }
    }

    return (
        <form onSubmit={submit} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 className="text-lg font-medium text-gray-900 mb-4">{title}</h2>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700">Vehicle (plate #)</label>
                    <select value={data.vehicle_id} onChange={e => setData('vehicle_id', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border bg-white">
                        <option value="">Select vehicle…</option>
                        {vehicles.map(v => (
                            <option key={v.id} value={v.id}>{v.plate_number}</option>
                        ))}
                    </select>
                    {errors.vehicle_id && <p className="text-red-500 text-xs mt-1">{errors.vehicle_id}</p>}
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700">Driver</label>
                    <select value={data.driver_id} onChange={e => setData('driver_id', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border bg-white">
                        <option value="">Select driver…</option>
                        {drivers.map(d => (
                            <option key={d.id} value={d.id}>{d.name}</option>
                        ))}
                    </select>
                    {errors.driver_id && <p className="text-red-500 text-xs mt-1">{errors.driver_id}</p>}
                    <p className="text-xs text-gray-400 mt-1">Only drivers not currently on another trip are listed.</p>
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700">Corridor</label>
                    <select value={data.corridor_id} onChange={e => setData('corridor_id', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border bg-white">
                        <option value="">Select corridor…</option>
                        {corridors.map(c => (
                            <option key={c.id} value={c.id}>{c.name}</option>
                        ))}
                    </select>
                    {errors.corridor_id && <p className="text-red-500 text-xs mt-1">{errors.corridor_id}</p>}
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700">Cargo Type</label>
                    <input type="text" value={data.cargo_type} onChange={e => setData('cargo_type', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border" />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700">Departure Time</label>
                    <input type="datetime-local" value={data.departure_time} onChange={e => setData('departure_time', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border" />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700">Expected Arrival</label>
                    <input type="datetime-local" value={data.expected_arrival} onChange={e => setData('expected_arrival', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border" />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700">Geofence Radius (m)</label>
                    <input type="number" value={data.geofence_radius_m} onChange={e => setData('geofence_radius_m', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border" />
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 pt-2">
                <MapPicker
                    label="Origin — click the map to set pickup location"
                    lat={data.origin_lat}
                    lng={data.origin_lng}
                    onChange={(lat, lng) => setData({ origin_lat: lat, origin_lng: lng })}
                />
                <MapPicker
                    label="Destination — click the map to set drop-off location"
                    lat={data.destination_lat}
                    lng={data.destination_lng}
                    onChange={(lat, lng) => setData({ destination_lat: lat, destination_lng: lng })}
                />
            </div>

            <div className="flex justify-end pt-2">
                <button type="submit" disabled={processing}
                    className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50">
                    {processing ? 'Saving...' : submitLabel}
                </button>
            </div>
        </form>
    )
}
