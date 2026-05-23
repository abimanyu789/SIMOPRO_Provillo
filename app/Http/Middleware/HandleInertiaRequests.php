<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Get shop settings
        $settings = \App\Models\Pengaturan::first();
        if ($settings) {
            $settings->logo_url = $settings->logo_url; // trigger accessor
        }

        // Get active user with accessor trigger
        $user = $request->user();
        if ($user) {
            $user->foto_url = $user->foto_url; // trigger accessor
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
            'pengaturanUsaha' => $settings,
        ];
    }
}
