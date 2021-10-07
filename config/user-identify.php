<?php
// config for Chuoke/UserIdentify
return [
    'table' => [
        'name' => 'user_identifiers',
        'foreign_key' => 'user_id',
        'owner_key' => 'id',
    ],

    'idetifier_model' => Chuoke\UserIdentify\Models\UserIdentifier::class,

    'user_model' => App\Models\User::class,

    'auth_provider_name' => 'user_identify',
];
