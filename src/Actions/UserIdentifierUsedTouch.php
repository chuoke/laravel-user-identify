<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\Models\UserIdentifier;

class UserIdentifierUsedTouch
{
    public function execute(UserIdentifier $userIdentifier)
    {
        return $userIdentifier->touch('last_used_at');
    }
}
