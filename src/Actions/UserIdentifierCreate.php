<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\Datas\UserIdentifierCreateData;
use Chuoke\UserIdentify\Exceptions\UserIdentifierExistsException;
use Chuoke\UserIdentify\Models\UserIdentifier;
use Chuoke\UserIdentify\Traits\CreateUserIdentifyModel;
use Illuminate\Foundation\Auth\User;

class UserIdentifierCreate
{
    use CreateUserIdentifyModel;

    /** @var User */
    protected $user;

    /** @var UserIdentifierCreateData */
    protected $data;

    /**
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @param  \Chuoke\UserIdentify\Datas\UserIdentifierCreateData  $data
     * @param  boolean  $checkExist
     * @return UserIdentifier
     */
    public function execute(User $user, UserIdentifierCreateData $data, $checkExist = true)
    {
        $this->data = $data;
        $this->user = $user;

        if ($checkExist) {
            $this->checkExists();
        }

        return $this->create();
    }

    protected function create(): UserIdentifier
    {
        return $this->createUserIdentifyModel()->create($this->buildCreateData());
    }

    protected function buildCreateData(): array
    {
        if (!$this->data->type) {
            throw new \InvalidArgumentException('The type must be specified.');
        }

        if ($this->data->passwordable && !$this->data->credential) {
            $this->data->credential = $this->user->getAuthPassword();
        }

        return [
            UserIdentifier::associateUserKey() => $this->user->getKey(),
            'type' => $this->data->type,
            'identifier' => $this->data->identifier,
            'credential' => $this->data->credential ?: null,
            'passwordable' => $this->data->passwordable ?: false,
            'verified_at' => $this->data->verified_at ?: null,
            'last_used_at' => date('Y-m-d H:i:s'),
        ];
    }

    protected function checkExists()
    {
        if ($this->getExists()) {
            throw new UserIdentifierExistsException($this->data->type);
        }
    }

    /**
     * @return UserIdentifier|null
     */
    protected function getExists()
    {
        return (new UserIdentifierFind)->execute(
            $this->data->type,
            $this->data->identifier,
            $this->user,
            $this->data->credential
        );
    }
}
