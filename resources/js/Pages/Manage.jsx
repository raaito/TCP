import { Link, useForm, usePage } from '@inertiajs/react'

function Section({ title, description, children }) {
    return (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-lg font-medium text-gray-900">{title}</h2>
                {description && <p className="text-sm text-gray-500 mt-0.5">{description}</p>}
            </div>
            <div className="p-6">{children}</div>
        </div>
    )
}

function DriverForm() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        phone_number: '',
    })

    return (
        <form onSubmit={e => { e.preventDefault(); post('/drivers') }} className="flex flex-col sm:flex-row gap-3">
            <div className="flex-1">
                <input
                    type="text"
                    placeholder="Driver name"
                    value={data.name}
                    onChange={e => setData('name', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                />
                {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
            </div>
            <div className="flex-1">
                <input
                    type="text"
                    placeholder="Phone number (driver ID for USSD)"
                    value={data.phone_number}
                    onChange={e => setData('phone_number', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                />
                {errors.phone_number && <p className="text-red-500 text-xs mt-1">{errors.phone_number}</p>}
            </div>
            <button
                type="submit"
                disabled={processing}
                className="mt-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 self-start sm:self-auto"
            >
                {processing ? 'Adding…' : 'Add driver'}
            </button>
        </form>
    )
}

function VehicleForm() {
    const { data, setData, post, processing, errors } = useForm({
        plate_number: '',
        capacity_type: '',
    })

    return (
        <form onSubmit={e => { e.preventDefault(); post('/vehicles') }} className="flex flex-col sm:flex-row gap-3">
            <div className="flex-1">
                <input
                    type="text"
                    placeholder="Plate number (e.g. LAG-458-AK)"
                    value={data.plate_number}
                    onChange={e => setData('plate_number', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                />
                {errors.plate_number && <p className="text-red-500 text-xs mt-1">{errors.plate_number}</p>}
            </div>
            <div className="flex-1">
                <input
                    type="text"
                    placeholder="Capacity (e.g. 10-ton truck)"
                    value={data.capacity_type}
                    onChange={e => setData('capacity_type', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                />
                {errors.capacity_type && <p className="text-red-500 text-xs mt-1">{errors.capacity_type}</p>}
            </div>
            <button
                type="submit"
                disabled={processing}
                className="mt-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50 self-start sm:self-auto"
            >
                {processing ? 'Adding…' : 'Add vehicle'}
            </button>
        </form>
    )
}

function CorridorForm() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        origin: '',
        destination: '',
        waypoints: '',
    })

    return (
        <form onSubmit={e => { e.preventDefault(); post('/corridors') }} className="space-y-3">
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <input
                        type="text"
                        placeholder="Corridor name (e.g. Lagos–Accra)"
                        value={data.name}
                        onChange={e => setData('name', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                    />
                    {errors.name && <p className="text-red-500 text-xs mt-1">{errors.name}</p>}
                </div>
                <div>
                    <input
                        type="text"
                        placeholder="Origin (e.g. Lagos)"
                        value={data.origin}
                        onChange={e => setData('origin', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                    />
                    {errors.origin && <p className="text-red-500 text-xs mt-1">{errors.origin}</p>}
                </div>
                <div>
                    <input
                        type="text"
                        placeholder="Destination (e.g. Accra)"
                        value={data.destination}
                        onChange={e => setData('destination', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                    />
                    {errors.destination && <p className="text-red-500 text-xs mt-1">{errors.destination}</p>}
                </div>
            </div>
            <div>
                <input
                    type="text"
                    placeholder="Waypoints, comma separated (e.g. Cotonou, Lomé, Aflao) — optional"
                    value={data.waypoints}
                    onChange={e => setData('waypoints', e.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border"
                />
                {errors.waypoints && <p className="text-red-500 text-xs mt-1">{errors.waypoints}</p>}
            </div>
            <div className="flex justify-end">
                <button
                    type="submit"
                    disabled={processing}
                    className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    {processing ? 'Adding…' : 'Add corridor'}
                </button>
            </div>
        </form>
    )
}

function List({ items, empty, render }) {
    if (items.length === 0) {
        return <p className="text-sm text-gray-500">{empty}</p>
    }
    return (
        <ul className="divide-y divide-gray-100 mt-3">
            {items.map(item => (
                <li key={item.id} className="py-2 text-sm text-gray-900">{render(item)}</li>
            ))}
        </ul>
    )
}

export default function Manage() {
    const { drivers, vehicles, corridors } = usePage().props

    return (
        <div className="space-y-8">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-xl font-semibold text-gray-900">Manage fleet & corridors</h1>
                    <p className="text-sm text-gray-500 mt-0.5">Add drivers, vehicles, and corridors here — they'll appear in the create-trip form.</p>
                </div>
                <Link href="/dashboard" className="text-sm text-gray-500 hover:text-gray-700">← Back to dashboard</Link>
            </div>

            <Section title="Drivers" description="Phone number doubles as the driver ID for the USSD ping.">
                <DriverForm />
                <List items={drivers} empty="No drivers yet." render={d => <span>{d.name} · <span className="text-gray-500">{d.phone_number}</span></span>} />
            </Section>

            <Section title="Vehicles" description="Plate numbers are matched loosely (spaces/dashes ignored).">
                <VehicleForm />
                <List items={vehicles} empty="No vehicles yet." render={v => <span>{v.plate_number}{v.capacity_type ? <span className="text-gray-500"> · {v.capacity_type}</span> : null}</span>} />
            </Section>

            <Section title="Corridors" description="Corridors are shared across the whole system.">
                <CorridorForm />
                <List items={corridors} empty="No corridors yet." render={c => (
                    <span>
                        {c.name}
                        <span className="text-gray-500"> — {c.origin} → {c.destination}</span>
                        {c.waypoints?.length > 0 && <span className="text-gray-400"> · {c.waypoints.join(', ')}</span>}
                    </span>
                )} />
            </Section>
        </div>
    )
}