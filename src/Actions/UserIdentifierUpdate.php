<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\Datas\UserIdentifierUpdateData;
use Chuoke\UserIdentify\Models\UserIdentifier;

class UserIdentifierUpdate
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
            'passwordable' => is_null($this->data->passwordable) ? $this->userIdentifier->passwordable : $this->data->passwordable,
            'verified_at' => is_null($this->data->verified_at) ? $this->userIdentifier->verified_at : $this->data->verified_at,
        ];
    }
}
