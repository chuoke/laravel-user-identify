<?php

namespace Chuoke\UserIdentify;

use Illuminate\Support\Facades\Auth;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class UserIdentifyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /**
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-user-identify')
            ->hasConfigFile('user-identify')
            ->hasMigration('create_user_identifiers_table');
    }

    public function packageBooted()
    {
        // Auth::provider($config('user-identify.auth_provider_name') ?: 'user-identify', function ($app) {
        //     return new UserIdentifyProvider(
        //         $app['hash'],
        //         config('user-identify.user_model'),
        //         config('user-identify.idetifier_model'),
        //     );
        // });
    }
}
