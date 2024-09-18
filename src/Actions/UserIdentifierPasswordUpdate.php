<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\AuthenticatableWithUserIdentify;
use Illuminate\Foundation\Auth\User;

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
