<?php

namespace Chuoke\UserIdentify;

use Chuoke\UserIdentify\Actions\UserIdentifierFind;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

trait CanResetPasswordWithUserrIdentify
{
    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        $emailIdentifier = (new UserIdentifierFind())
            ->execute('email', null, $this);

        return $emailIdentifier ? $emailIdentifier->identifier : '';
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
