import { createInertiaApp } from '@inertiajs/react'
import { createRoot } from 'react-dom/client'
import DashboardLayout from './Layouts/DashboardLayout'

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true })
        let page = pages[`./Pages/${name}.jsx`]

        if (name !== 'Login' && !page.default.layout) {
            page.default.layout = page => <DashboardLayout children={page} />
        }

        return page
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />)
    },
})
