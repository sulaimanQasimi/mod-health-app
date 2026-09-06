<?php

namespace App\Http\Middleware;

use App\Services\SidebarMenuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
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
        $locale = session('language', 'dr');
        $user = $request->user();

        return [
            ...parent::share($request),
            'locale' => $locale,
            'direction' => $locale === 'en' ? 'ltr' : 'rtl',
            'translations' => Lang::get('global', [], $locale),
            'activityLogTranslations' => Lang::get('activity_log', [], $locale),
            'sidebarMenu' => app(SidebarMenuService::class)->build($request),
            'currentRoute' => $request->route()?->getName(),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name_dr ?? $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : asset('assets/img/avatars/1.png'),
                ] : null,
            ],
            'csrfToken' => csrf_token(),
            // Named appUrls so page-level `urls` props do not overwrite navbar links.
            'appUrls' => [
                'changeLanguage' => url('change_language'),
                'profile' => route('react.profile.show'),
                'logout' => url('/logout'),
                'chats' => url('/chats'),
            ],
        ];
    }
}
