<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\Models\UserIdentifier;

class UserIdentifierVerifiedMark
{
    public function execute(UserIdentifier $userIdentifier, $verified = true)
    {
        if ($verified) {
            return $userIdentifier->touch('verified_at');
        }

        return $userIdentifier->update([
            'verified_at' => null,
        ]);
    }
}
