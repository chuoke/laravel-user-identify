<?php

namespace Chuoke\UserIdentify;

use Chuoke\UserIdentify\Actions\UserIdentifierFind;
use Chuoke\UserIdentify\Models\UserIdentifier;

trait AuthenticatableWithUserIdentify
{
    /**
     * The column name of the password field using during authentication.
     *
     * @var string
     */
    protected $authPasswordName = 'password';

    /**
     * The column name of the "remember me" token.
     *
     * @var string
     */
    protected $rememberTokenName = 'remember_token';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function identifiers()
    {
        return $this->hasMany(
            \config('user-identify.idetifier_model'),
            UserIdentifier::associateUserKey(),
            \config('user-identify.user_key')
        );
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the unique broadcast identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifierForBroadcasting()
    {
        return $this->getAuthIdentifier();
    }

    /**
     * Get the name of the password attribute for the user.
     *
     * @return string
     */
    public function getAuthPasswordName()
    {
        return $this->authPasswordName;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        if ($this->isPasswordStillInUser() && $this->hasPasswordAttr()) {
            return $this->{$this->getAuthPasswordName()};
        }

        $passwordIdentifier = $this->identifiers()
            ->where('passwordable', true)
            ->first();

        return $passwordIdentifier ? $passwordIdentifier->credential : '';
    }

    public static function isPasswordStillInUser()
    {
        return false;
    }

    public function hasPasswordAttr()
    {
        return ! empty($this->getAuthPasswordName()) && $this->hasAttribute($this->getAuthPasswordName());
        ;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        if ($this->isRememberTokenStillInUser() && $this->hasRememberTokenAttr()) {
            return (string) $this->{$this->getRememberTokenName()};
        }

        if ($rememberTokenIdentifier = $this->getRememberTokenIdentifier()) {
            return $rememberTokenIdentifier->credential;
        }

        return null;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        if ($this->isRememberTokenStillInUser() && $this->hasRememberTokenAttr()) {
            $this->{$this->getRememberTokenName()} = $value;

            return;
        }

        if ($rememberTokenIdentifier = $this->getRememberTokenIdentifier()) {
            $rememberTokenIdentifier->credential = $value;

            $this->setRelation($this->getRememberTokenIdentifierRelationName(), $rememberTokenIdentifier);
        }
    }

    public function hasRememberTokenAttr()
    {
        return ! empty($this->getRememberTokenName()) && $this->hasAttribute($this->getRememberTokenName());
    }

    public static function isRememberTokenStillInUser()
    {
        return false;
    }

    /**
     * @param  bool  $new
     * @return Models\UserIdentifier|null
     */
    public function getRememberTokenIdentifier($new = false)
    {
        $relationName = $this->getRememberTokenIdentifierRelationName();

        if ($new || ! $this->relationLoaded($relationName)) {
            $identifier = (new UserIdentifierFind())
                ->execute($this->getRememberTokenIdentifierTypeName(), $this->getKey(), $this);

            $this->setRelation($relationName, $identifier);

            return $identifier;
        }

        return $this->getRelation($relationName);
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return $this->rememberTokenName;
    }

    public function getRememberTokenIdentifierRelationName()
    {
        return 'remember_token_identifier';
    }

    public function getRememberTokenIdentifierTypeName()
    {
        return 'remember_token';
    }
}
