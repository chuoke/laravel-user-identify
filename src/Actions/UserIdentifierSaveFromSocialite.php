<?php

namespace Chuoke\UserIdentify\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model as User;
use Laravel\Socialite\AbstractUser;
use Chuoke\UserIdentify\Datas\UserIdentifierCreateData;

class UserIdentifierSaveFromSocialite
{
    /**
     * @param  \Laravel\Socialite\AbstractUser  $socialiteUser
     * @throws \InvalidArgumentException
     * @return \Chuoke\UserIdentify\Models\UserIdentifier
     */
    public function execute(AbstractUser $socialiteUser)
    {
        if (!array_key_exists('socialite_type', $socialiteUser->getRaw()) || !$socialiteUser['socialite_type']) {
            throw new \InvalidArgumentException('The [socialite_type] must be specified.');
        }

        return DB::transaction(function () use ($socialiteUser) {
            $socialiteType = $socialiteUser['socialite_type'];

            $socialiteIdIdentifier = (new UserIdentifierFind)->execute($socialiteType, $socialiteUser->getId());
            if ($email = $socialiteUser->getEmail()) {
                $emailIdentifier = (new UserIdentifierFind)->execute('email', $email);
            }

            $user = $socialiteIdIdentifier ? $socialiteIdIdentifier->user : null;

            if ($socialiteIdIdentifier && !$emailIdentifier && !$this->isUserHasEmailIdentifier($user)) {
                $emailIdentifier = $this->createEmailIdentifier($user, $socialiteUser->getEmail());
            } elseif (!$socialiteIdIdentifier && $emailIdentifier) {
                $user = $emailIdentifier->user;
                $socialiteIdIdentifier = $this->createSocialIdentifier($user, $socialiteType, $socialiteUser);
            }

            $user = (new (config('user-identify.actions.user_save_from_socialite'))())->execute($user, $socialiteUser);

            if (!$socialiteIdIdentifier) {
                $socialiteIdIdentifier = $this->createSocialIdentifier($user, $socialiteType, $socialiteUser);
            }

            if (!$emailIdentifier && $socialiteUser->getEmail() && !$this->isUserHasEmailIdentifier($user)) {
                $emailIdentifier = $this->createEmailIdentifier($user, $socialiteUser->getEmail());
            }

            $socialiteIdIdentifier->setRelation('user', $user);

            return $socialiteIdIdentifier;
        });
    }

    protected function isUserHasEmailIdentifier(User $user)
    {
        $emailIdentifier = (new UserIdentifierFind)->execute('email', null, $user);

        return !!$emailIdentifier;
    }

    protected function createEmailIdentifier(User $user, $email)
    {
        return (new UserIdentifierCreate())
            ->execute($user, new UserIdentifierCreateData([
                'type' => 'email',
                'identifier' => $email,
                'passwordable' => true,
                'verified_at' => date('Y-m-d H:i:s'),
            ]), false);
    }

    protected function createSocialIdentifier(User $user, $socialiteType, $socialiteUser)
    {
        return (new UserIdentifierCreate())
            ->execute($user, new UserIdentifierCreateData([
                'type' => $socialiteType,
                'identifier' => $socialiteUser->getId(),
                'credential' => $socialiteUser->token,
            ]), false);
    }
}
