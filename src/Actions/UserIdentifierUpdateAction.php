<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\Models\UserIdentifier;
use Chuoke\UserIdentify\Datas\UserIdentifierUpdateData;

class UserIdentifierUpdateAction
{
    /** @var UserIdentifier */
    protected $userIdentifier;

    /** @var UserIdentifierUpdateData */
    protected $data;

    public function execute(UserIdentifier $userIdentifier, UserIdentifierUpdateData $data)
    {
        $this->data = $data;
        $this->userIdentifier = $userIdentifier;

        return $this->update();
    }

    protected function update(): bool
    {
        return $this->userIdentifier->update($this->buildUpdateData());
    }

    protected function buildUpdateData(): array
    {
        return [
            'identifier' => $this->data->identifier,
            'credential' => $this->data->credential,
        ];
    }
}
