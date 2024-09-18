<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\Datas\UserIdentifierCreateData;
use Chuoke\UserIdentify\Datas\UserIdentifierUpdateData;
use Chuoke\UserIdentify\Models\UserIdentifier;
use Illuminate\Foundation\Auth\User;

class UserIdentifierUpdateOrCreate
{
    /** @var User */
    protected $user;

    /** @var UserIdentifierCreateData */
    protected $data;

    /**
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @param  \Chuoke\UserIdentify\Datas\UserIdentifierCreateData  $data
     * @return \Chuoke\UserIdentify\Models\UserIdentifier
     */
    public function execute(User $user, UserIdentifierCreateData $data)
    {
        $this->data = $data;
        $this->user = $user;

        if ($identifier = $this->getExists()) {
            (new UserIdentifierUpdate())
                ->execute(
                    $identifier,
                    new UserIdentifierUpdateData($data->toArray())
                );

            return $identifier;
        }

        return (new UserIdentifierCreate())
            ->execute($user, $data, false);
    }

    /**
     * @return UserIdentifier|null
     */
    protected function getExists()
    {
        return (new UserIdentifierFind())->execute(
            $this->data->type,
            $this->data->identifier,
            $this->user,
            $this->data->credential
        );
    }
}
