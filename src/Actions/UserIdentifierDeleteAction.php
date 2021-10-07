<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\Models\UserIdentifier;

class UserIdentifierDeleteAction
{
    /** @var UserIdentifier */
    protected $userIdentifier;

    public function execute(UserIdentifier $userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;

        return $this->delete();
    }

    protected function delete(): bool
    {
        return $this->userIdentifier->delete();
    }
}
