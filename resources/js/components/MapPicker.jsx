import { useEffect, useRef } from 'react'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const DEFAULT_CENTER = [6.5244, 3.3792]

const markerIcon = L.divIcon({
    className: '',
    html: '<div style="width:18px;height:18px;border-radius:50%;background:#4f46e5;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35)"></div>',
    iconSize: [18, 18],
    iconAnchor: [9, 9],
})

function formatCoord(value) {
    return value === null || value === undefined || value === '' ? null : Number(value)
}

export default function MapPicker({ label, lat, lng, onChange, height = 280 }) {
    const containerRef = useRef(null)
    const mapRef = useRef(null)
    const markerRef = useRef(null)
    const onChangeRef = useRef(onChange)
    onChangeRef.current = onChange

    const latNum = formatCoord(lat)
    const lngNum = formatCoord(lng)
    const hasPoint = latNum !== null && lngNum !== null

    useEffect(() => {
        if (!containerRef.current) return

        const center = hasPoint ? [latNum, lngNum] : DEFAULT_CENTER

        const map = L.map(containerRef.current, {
            center,
            zoom: hasPoint ? 13 : 6,
            scrollWheelZoom: false,
        })

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map)

        if (hasPoint) {
            markerRef.current = L.marker([latNum, lngNum], { icon: markerIcon, draggable: true }).addTo(map)
            markerRef.current.on('dragend', () => {
                const pos = markerRef.current.getLatLng()
                onChangeRef.current(round(pos.lat), round(pos.lng))
            })
        }

        map.on('click', e => {
            if (markerRef.current) {
                markerRef.current.setLatLng(e.latlng)
            } else {
                markerRef.current = L.marker(e.latlng, { icon: markerIcon, draggable: true }).addTo(map)
                markerRef.current.on('dragend', () => {
                    const pos = markerRef.current.getLatLng()
                    onChangeRef.current(round(pos.lat), round(pos.lng))
                })
            }
            onChangeRef.current(round(e.latlng.lat), round(e.latlng.lng))
        })

        mapRef.current = map

        setTimeout(() => map.invalidateSize(), 0)

        return () => {
            if (mapRef.current) {
                mapRef.current.remove()
                mapRef.current = null
                markerRef.current = null
            }
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [])

    return (
        <div>
            <label className="block text-sm font-medium text-gray-700">{label}</label>
            <div ref={containerRef} style={{ height, width: '100%' }} className="mt-1 rounded-md border border-gray-300 z-0" />
            <p className="text-xs text-gray-500 mt-1">
                {hasPoint ? `Selected: ${latNum}, ${lngNum}` : 'Click the map to set the location, then drag the marker to fine-tune.'}
            </p>
        </div>
    )
}

function round(n) {
    return Number(n.toFixed(6))
}