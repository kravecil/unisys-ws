<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::viaRequest('custom', function ($request) {
            $response = Http::acceptJson()
                ->withToken($request->bearerToken())
                ->timeout(5)
                ->post(config('app.unisys_host_auth') . '/api/token');

            if ($response->successful()) {
                $contents = json_decode(json: $response->getBody()->getContents());

                $user = new User();
                $user->id = $contents->id;
                $user->permissions = collect($contents->permissions);
                $user->token = $request->bearerToken();

                return $user;
            }
            abort(401, 'Пользователь не прошёл аутентификацию');
        });
    }
}
