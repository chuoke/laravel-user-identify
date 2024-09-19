# Laravel multi identifier auth provider to thin your user model/table.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chuoke/laravel-user-identify.svg?style=flat-square)](https://packagist.org/packages/chuoke/laravel-user-identify)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/chuoke/laravel-user-identify/run-tests?label=tests)](https://github.com/chuoke/laravel-user-identify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/chuoke/laravel-user-identify/Check%20&%20fix%20styling?label=code%20style)](https://github.com/chuoke/laravel-user-identify/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/chuoke/laravel-user-identify.svg?style=flat-square)](https://packagist.org/packages/chuoke/laravel-user-identify)

---

## Why this?

> In-depth learning and understanding about Laravel by transforming its features.

Nowadays, there are many ways to log in, especially social login has become very popular. Usually we would add login identifiers to the `users` table, and while this is makes the use of this identification information and access to Laravel's existing logic simple. But this makes the `users` table very bloated, and these login identifiers are usually of little use in business logic. In order to simplify the `users` table and More convenient way to add login ways. I created this package.

With the help of this package, my `user` table is concise.

```sql
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
)
```

The `users` table's `email`, `password`, `remember_token`, and any social login ids are converted into the identifier credentials table `user_identifiers`.

If you need to add other authentication identifier, just add one record.

## Installation

> Not fully available !!!!
>
> At present, I only use a some login method based on this package, welcome to improve it.

You can install the package via composer:

```bash
composer require chuoke/laravel-user-identify
```

```bash
php artisan vendor:publish --provider="Chuoke\UserIdentify\UserIdentifyServiceProvider"
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="user-identify-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="user-identify-config"
```

## Config

This is the contents of the published config file:

In `config/user-identify.php`:

```php
return [
    'idetifier_model' => Chuoke\UserIdentify\Models\UserIdentifier::class,
    'idetifier_table' => 'user_identifiers',
    'idetifier_user_key' => 'user_id',

    'user_model' => App\Models\User::class,
    'user_key' => 'id',

    'auth_provider_name' => 'user_identify',

    'actions' => [
        'user_save_from_socialite' => \Chuoke\UserIdentify\Actions\UserSaveFromSocialite::class,
    ],
];
```

Add `user_identify` guard provider to `auth.php` config file like this:

In `config/auth.php`:

```php
return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'user_identify',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'user_identify' => [
            'driver' => config('user-identify.auth_provider_name', 'user_identify'),
        ],
    ],
    // ...
];

```

Next, register auth provider:

In `AuthServiceProvider.php`:

```php

use Chuoke\UserIdentify\UserIdentifyProvider;
use Illuminate\Contracts\Foundation\Application;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Auth::provider(config('user-identify.auth_provider_name'), function (Application $app, array $config) {
            return new UserIdentifyProvider(
                $app['hash'],
                config('user-identify.user_model'),
                config('user-identify.idetifier_model'),
            );
        });
    }
}
```

## Usage

Sign in with GitHub account.

```php
<?php

namespace App\Web\Controllers\Auth;

use Illuminate\Http\Request;
use App\Web\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GithubController extends Controller
{
    public function redirect(Request $request)
    {
        return Socialite::driver('github')->redirect();
    }

    public function callback(Request $request)
    {
        /** @var \Laravel\Socialite\AbstractUser $githubUser */
        $githubUser = Socialite::driver('github')->user();

        $githubUser['socialite_type'] = 'github';

        $successful = Auth::attempt([
            'identifier' => $githubUser,
        ], true);

        if ($successful) {
            return redirect('/dashboard');
        }

        return redirect('/login');
    }
}
```

If the user does not exist, it is automatically created.

> And you can see the source code for more details.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
