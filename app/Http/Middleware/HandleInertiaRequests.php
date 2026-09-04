<?php

namespace App\Http\Middleware;

use App\Models\DispatcherUser;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function rootView(Request $request): string
    {
        return 'app';
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
        ];
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }
}
