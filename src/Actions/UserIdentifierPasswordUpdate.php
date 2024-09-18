<?php

namespace Chuoke\UserIdentify\Actions;

use Illuminate\Foundation\Auth\User;
use Chuoke\UserIdentify\AuthenticatableWithUserIdentify;

class UserIdentifierPasswordUpdate
{

    public function execute(User $user, $hashedPassword)
    {
        /** @var AuthenticatableWithUserIdentify $user */
        $user->identifiers()->where('passwordable', true)->all()
            ->each(function ($identifier) use ($hashedPassword) {
                $identifier->forceFill([
                    'credential' => $hashedPassword,
                ])->save();
            });
    }
}
