<?php

namespace Chuoke\UserIdentify\Actions;

use Illuminate\Database\Eloquent\Model as User;
use Laravel\Socialite\AbstractUser;

class UserSaveFromSocialite
{
    /**
     * @param  \Illuminate\Database\Eloquent\Model|null  $user
     * @param  \Laravel\Socialite\AbstractUser  $socialiteUser
     * @return User|null
     */
    public function execute(User $user = null, AbstractUser $socialiteUser)
    {
        if ($user) {
            $user->update($this->buildSaveData($socialiteUser));
        } else {
            $user = $this->makeUserModel()
                ->create($this->buildSaveData($socialiteUser));
        }

        return $user;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function makeUserModel()
    {
        $userModel = config('user-identify.user_model');

        $userModelClass = '\\' . ltrim($userModel, '\\');

        return new $userModelClass();
    }

    protected function buildSaveData(AbstractUser $socialiteUser): array
    {
        return [
            'name' => $socialiteUser->getName(),
            'avatar' => $socialiteUser->getAvatar() ?: '',
        ];
    }
}
