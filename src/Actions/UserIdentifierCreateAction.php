<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\DTOs\UserIdentifierCreateData;
use Chuoke\UserIdentify\Exceptions\UserIdentifierExistsException;
use Chuoke\UserIdentify\Models\UserIdentifier;
use Illuminate\Foundation\Auth\User;

class UserIdentifierCreateAction
{
    /** @var User */
    protected $user;

    /** @var UserIdentifierCreateData */
    protected $data;

    public function execute(UserIdentifierCreateData $data, User $user)
    {
        $this->data = $data;
        $this->user = $user;

        $this->checkExists();

        return $this->create();
    }

    protected function create(): UserIdentifier
    {
        return UserIdentifier::create($this->buildCreateData());
    }

    protected function buildCreateData(): array
    {
        return [
            $this->userIdentifierFreignKey() => $this->user->getKey(),
            'type' => $this->data->type,
            'identifier' => $this->data->identifier,
            'credential' => $this->data->credential,
        ];
    }

    protected function checkExists()
    {
        if (UserIdentifier::where([
            $this->userIdentifierFreignKey() => $this->user->getKey(),
            'type' => $this->data->type,
            'identifier' => $this->data->identifier,
            'credential' => $this->data->credential,
        ])->first()) {
            throw new UserIdentifierExistsException();
        }
    }

    protected function userIdentifierFreignKey()
    {
        return config('user-identify.table.foreign_key');
    }
}
