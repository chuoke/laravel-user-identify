<?php

namespace Chuoke\UserIdentify\Actions;

use Chuoke\UserIdentify\Models\UserIdentifier;
use Chuoke\UserIdentify\Traits\CreateUserIdentifyModel;
use Illuminate\Foundation\Auth\User;

class UserIdentifierFind
{
    use CreateUserIdentifyModel;

    /**
     * @param  string  $type
     * @param  string  $identifier
     * @param  string|null  $credential
     * @param  \Illuminate\Foundation\Auth\User  $user
     * @return UserIdentifier|null
     */
    public function execute($type, $identifier = null, User $user = null, $credential = null)
    {
        if (!$identifier && !$user) {
            throw new \InvalidArgumentException('The [identifier] and [user] arguments provide at least one.');
        }

        $query = $this->createUserIdentifyModel()->newQuery()
            ->where([
                'type' => $type,
            ]);

        if ($identifier) {
            if (is_array($identifier) || $identifier instanceof \Illuminate\Contracts\Support\Arrayable) {
                $query->whereIn('identifier', $identifier);
            } else {
                $query->where('identifier', $identifier);
            }
        }

        if ($credential) {
            $query->where('credential', $credential);
        }

        if ($user) {
            $query->where(UserIdentifier::associateUserKey(), $user->getKey());
        }

        return $query->first();
    }
}
