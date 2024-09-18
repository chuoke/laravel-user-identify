<?php

namespace Chuoke\UserIdentify;

use Illuminate\Auth\Notifications\VerifyEmail;
use Chuoke\UserIdentify\Actions\UserIdentifierFind;
use Chuoke\UserIdentify\Actions\UserIdentifierVerifiedMark;

trait MustVerifyEmailWithUserIdentify
{
    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        $emailIdentifier = $this->getEmailIdentifier();

        return ! is_null($emailIdentifier ? $emailIdentifier->verified_at : null);
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        $emailIdentifier = $this->getEmailIdentifier();

        if ($emailIdentifier) {
            return (new UserIdentifierVerifiedMark)->execute($emailIdentifier);
        }

        return false;
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail);
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        $emailIdentifier = $this->getEmailIdentifier();

        return $emailIdentifier ? $emailIdentifier->identifier : '';
    }

    /**
     * @param  boolean  $new
     * @return Models\UserIdentifier|null
     */
    public function getEmailIdentifier($new = false)
    {
        $name = $this->getEmailIdentifierRelationName();
        if ($new || !$this->relationLoaded($name)) {
            $this->setRelation(
                $name,
                (new UserIdentifierFind)->execute('email', null, $this)
            );
        }

        return $this->getRelation($name);
    }

    public function getEmailIdentifierRelationName()
    {
        return 'email_identifier';
    }
}
