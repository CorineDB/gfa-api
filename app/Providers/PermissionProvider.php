<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PermissionProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            Permission::get()->map(function ($permission) {
                Gate::define($permission->slug, function ($user, $model = null) use ($permission) {

                    $response = $user->hasPermissionTo($permission->slug);

                    if($model && !($user->hasRole("unitee-de-gestion")))
                    {
                        if(str_contains($permission->slug, 'modifier-un-bailleur')){
                            return $response && ($model->id === $user->profilable->id) && ($model === $user->profilable);
                        }
                    }
                    return $response;
                });
            });
        } catch (\Exception $e) {
            report($e);
            return false;
        }
    }
}
