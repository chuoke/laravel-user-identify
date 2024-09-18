<?php
// config for Chuoke/UserIdentify
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
