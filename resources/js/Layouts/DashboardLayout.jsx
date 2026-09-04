import { Link, useForm, usePage } from '@inertiajs/react'

export default function DashboardLayout({ children }) {
    const { auth } = usePage().props
    const { post } = useForm()

    function logout(e) {
        e.preventDefault()
        post('/logout')
    }

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="bg-white border-b border-gray-200">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <Link href="/dashboard" className="text-xl font-semibold text-gray-900 hover:text-indigo-600">
                        TradeCorridor
                    </Link>
                    <div className="flex items-center gap-4">
                        {auth?.user ? (
                            <>
                                <Link href="/manage" className="text-sm text-gray-600 hover:text-indigo-600">Manage</Link>
                                <span className="text-sm text-gray-600">{auth.user.name}</span>
                                <form onSubmit={logout}>
                                    <button type="submit" className="text-sm text-gray-500 hover:text-gray-700">
                                        Sign out
                                    </button>
                                </form>
                            </>
                        ) : (
                            <Link href="/login" className="text-sm text-gray-500 hover:text-gray-700">Sign in</Link>
                        )}
                    </div>
                </div>
            </header>
            <main className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                {children}
            </main>
        </div>
    )
}
