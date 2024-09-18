<?php

namespace Chuoke\UserIdentify;

use Chuoke\UserIdentify\Actions\UserIdentifierCreate;
use Chuoke\UserIdentify\Actions\UserIdentifierPasswordUpdate;
use Chuoke\UserIdentify\Actions\UserIdentifierSaveFromSocialite;
use Chuoke\UserIdentify\Actions\UserIdentifierUsedTouch;
use Chuoke\UserIdentify\Datas\UserIdentifierCreateData;
use Closure;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Support\Arrayable;

class UserIdentifyProvider extends EloquentUserProvider
{
    /**
     * The Eloquent user auth model.
     *
     * @var string
     */
    protected $userIdentifyModel;

    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $userModel
     * @param  string  $userIdentifyModel
     * @return void
     */
    public function __construct(HasherContract $hasher, $userModel, $userIdentifyModel)
    {
        parent::__construct($hasher, $userModel);

        $this->userIdentifyModel = $userIdentifyModel;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable&AuthenticatableWithUserIdentify|null
     */
    public function retrieveById($identifier)
    {
        return parent::retrieveById($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $retrievedModel = $this->retrieveById($identifier);

        if (! $retrievedModel) {
            return;
        }

        if ($retrievedModel->isRememberTokenStillInUser()) {
            $rememberToken = $retrievedModel->getRememberToken();
        } else {
            $rememberTokenIdentifier = $retrievedModel->getRememberTokenIdentifier();

            $rememberToken = $rememberTokenIdentifier ? $rememberTokenIdentifier->credential : null;
        }

        return $rememberToken && hash_equals($rememberToken, $token)
            ? $retrievedModel : null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(UserContract $user, $token)
    {
        /** @var \Illuminate\Foundation\Auth\User $user */

        if ($user->isRememberTokenStillInUser()) {
            parent::updateRememberToken($user, $token);

            return;
        }

        $rememberTokenIdentifier = $user->getRememberTokenIdentifier();

        if ($rememberTokenIdentifier) {
            $rememberTokenIdentifier->credential = $token;
            $rememberTokenIdentifier->save();
        } else {
            $rememberTokenIdentifier = (new UserIdentifierCreate())
                ->execute(
                    $user,
                    new UserIdentifierCreateData([
                        'type' => $user->getRememberTokenIdentifierTypeName(),
                        'identifier' => $user->getKey(),
                        'credential' => $token,
                    ])
                );
        }

        $user->setRelation($user->getRememberTokenIdentifierRelationName(), $rememberTokenIdentifier);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (
            $this->createUserModel()->isPasswordStillInUser()
            && ($user = parent::retrieveByCredentials($credentials))
        ) {
            return $user;
        }

        if (array_key_exists('email', $credentials)) {
            $conditions['type'] = 'email';
            $conditions['identifier'] = $credentials['email'];
        } else {
            $conditions = array_filter(
                $credentials,
                fn ($key) => in_array($key, ['type', 'identifier']),
                ARRAY_FILTER_USE_KEY
            );
        }

        if (empty($conditions)) {
            return;
        }

        if ($conditions['identifier'] instanceof \Laravel\Socialite\AbstractUser) {
            $userIdentifier = $this->retrieveUserIdentifier([
                'type' => $conditions['identifier']['socialite_type'],
                'identifier' => $conditions['identifier']->getId(),
            ]);

            if (! $userIdentifier && ($email = $conditions['identifier']->getEmail())) {
                $userIdentifier = $this->retrieveUserIdentifier([
                    'type' => 'email',
                    'identifier' => $email,
                ]);
            }

            $userIdentifier = (new UserIdentifierSaveFromSocialite())->execute($conditions['identifier']);
            if (! $userIdentifier) {
            }
        } else {
            $userIdentifier = $this->retrieveUserIdentifier($conditions);
        }

        if (! $userIdentifier) {
            return null;
        }

        (new UserIdentifierUsedTouch())->execute($userIdentifier);

        $user = $userIdentifier->user;

        $user->setRelation('user_identifier', $userIdentifier->withoutRelations());

        return $user;
    }

    /**
     * @param  array  $conditions
     * @return Models\UserIdentifier|null
     */
    protected function retrieveUserIdentifier(array $conditions)
    {
        if (
            ! array_key_exists('type', $conditions) || ! $conditions['type']
            || ! array_key_exists('identifier', $conditions) || ! $conditions['identifier']
        ) {
            // throw new \InvalidArgumentException('The "type" property must be specified.');
            return;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->newUserIdentifyModelQuery();

        foreach ($conditions as $key => $value) {
            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } elseif ($value instanceof Closure) {
                $value($query);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable&AuthenticatableWithUserIdentify  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        if ($user->isPasswordStillInUser()) {
            return parent::validateCredentials($user, $credentials);
        }

        /** @var \Illuminate\Foundation\Auth\User $user */

        $plain = $credentials['password'] ?? $credentials['credential'] ?? null;

        if (! $user->relationLoaded('user_identifier')) {
            return false;
        }

        $result = $user->user_identifier->check($plain, $this->hasher);

        if ($result) {
            (new UserIdentifierUsedTouch())->execute($user->user_identifier);
        }

        return $result;
    }

    /**
     * Rehash the user's password if required and supported.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable&AuthenticatableWithUserIdentify  $user
     * @param  array  $credentials
     * @param  bool  $force
     * @return void
     */
    public function rehashPasswordIfRequired(UserContract $user, #[\SensitiveParameter] array $credentials, bool $force = false)
    {
        if ($user->isPasswordStillInUser()) {
            parent::rehashPasswordIfRequired($user, $credentials, $force);
        }

        if (! $this->hasher->needsRehash($user->getAuthPassword()) && ! $force) {
            return;
        }

        if (! array_key_exists('password', $credentials)) {
            return;
        }

        $this->updatePassword($user, $credentials);
    }

    protected function updatePassword(UserContract $user, array $credentials)
    {
        (new UserIdentifierPasswordUpdate())
            ->execute(
                $user,
                $this->hasher->make($credentials['password'])
            );
    }

    /**
     * Get a new query builder for the model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newModelQuery($model = null)
    {
        return is_null($model)
            ? $this->createModel()->newQuery()
            : $model->newQuery();
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        return $this->createUserModel();
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createUserModel()
    {
        $class = '\\' . ltrim($this->model, '\\');

        return new $class();
    }

    /**
     * Get a new query builder for the model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newUserIdentifyModelQuery($model = null)
    {
        return is_null($model)
            ? $this->createUserIdentifyModel()->newQuery()
            : $model->newQuery();
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createUserIdentifyModel()
    {
        $class = '\\' . ltrim($this->userIdentifyModel, '\\');

        return new $class();
    }

    /**
     * Gets the hasher implementation.
     *
     * @return \Illuminate\Contracts\Hashing\Hasher
     */
    public function getHasher()
    {
        return $this->hasher;
    }

    /**
     * Sets the hasher implementation.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @return $this
     */
    public function setHasher(HasherContract $hasher)
    {
        $this->hasher = $hasher;

        return $this;
    }

    /**
     * Gets the name of the Eloquent user model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sets the name of the Eloquent user model.
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Gets the name of the user identify model.
     *
     * @return string
     */
    public function getUserIdentifyModel()
    {
        return $this->userIdentifyModel;
    }

    /**
     * Sets the name of the user identify model.
     *
     * @param  string  $model
     * @return $this
     */
    public function setUserIdentifyModel($model)
    {
        $this->userIdentifyModel = $model;

        return $this;
    }
}
