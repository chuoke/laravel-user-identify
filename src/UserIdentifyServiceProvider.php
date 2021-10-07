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
            ->hasConfigFile()
            ->hasMigration('create_user_identifiers_table');
    }

    public function packageBooted()
    {
        // Auth::provider($this->app['config']['identify']['auth_provider_name'], function ($app, array $config) {
        //     return new UserIdentifyProvider(
        //         $app['hash'],
        //         $config['model'],
        //         $app['config']['identify']['user_model']
        //     );
        // });
    }
}
