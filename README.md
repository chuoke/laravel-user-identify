# Laravel multi auth ways.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chuoke/laravel-user-identify.svg?style=flat-square)](https://packagist.org/packages/chuoke/laravel-user-identify)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/chuoke/laravel-user-identify/run-tests?label=tests)](https://github.com/chuoke/laravel-user-identify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/chuoke/laravel-user-identify/Check%20&%20fix%20styling?label=code%20style)](https://github.com/chuoke/laravel-user-identify/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/chuoke/laravel-user-identify.svg?style=flat-square)](https://packagist.org/packages/chuoke/laravel-user-identify)

---

> NOT READY!!!!!!

## Installation

You can install the package via composer:

```bash
composer require chuoke/laravel-user-identify
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --provider="Chuoke\UserIdentify\UserIdentifyServiceProvider" --tag="laravel-user-identify-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Chuoke\UserIdentify\UserIdentifyServiceProvider" --tag="laravel-user-identify-config"
```

This is the contents of the published config file:

```php
return [
];
```

Add `user_identify` guard provider to `auth.php` config file like this:

```php

// config/auth.php
return [

    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt',
            'provider' => 'user_identify',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'user_identify' => [
            'driver' => 'user_identify',
            'model' => Chuoke\UserIdentify\Models\UserIdentify::class, // user identify model
        ],
    ],
    // ...
];

// AuthServiceProvider.php
Auth::provider($this->app['config']['identify']['auth_provider_name'] /* or 'user_identify' */, function ($app, array $config) {
    return new UserIdentifyProvider(
        $app['hash'],
        $config['model'],
        $app['config']['identify']['user_model']
    );
});

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
