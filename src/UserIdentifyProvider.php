<?php

namespace Chuoke\UserIdentify;

use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class UserIdentifyProvider implements UserProvider
{
    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The Eloquent user auth model.
     *
     * @var string
     */
    protected $userIdentifyModel;

    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $userModel;

    /**
     * Create a new database user provider.
     *
     * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
     * @param  string  $userIdentifyModel
     * @param  string  $userModel
     * @return void
     */
    public function __construct(HasherContract $hasher, $userIdentifyModel, $userModel)
    {
        $this->userIdentifyModel = $userIdentifyModel;
        $this->hasher = $hasher;
        $this->userModel = $userModel;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        $model = $this->createUserModel();

        return $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();
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
        $model = $this->createUserModel();

        $retrievedModel = $this->newModelQuery($model)
            ->where($model->getAuthIdentifierName(), $identifier)
            ->first();

        if (!$retrievedModel) {
            return;
        }

        $rememberToken = $retrievedModel->getRememberToken();

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
    public function updateRememberToken(Authenticatable $user, $token)
    {
        /** @var \Illuminate\Foundation\Auth\User $user */

        $user->setRememberToken($token);

        $timestamps = $user->timestamps;

        $user->timestamps = false;

        $user->save();

        $user->timestamps = $timestamps;
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        // Compatible with default of Laravel
        $credentialKeys = ['password', 'credential'];

        if (
            empty($credentials) ||
            (count($credentials) === 1 &&
                Str::contains($this->firstCredentialKey($credentials), $credentialKeys))
        ) {
            return;
        }

        // First we will add each credential element to the query as a where clause.
        // Then we can execute the query and, if we found a user, return it in a
        // Eloquent User "model" that will be utilized by the Guard instances.
        $query = $this->newModelQuery($this->createUserIdentifyModel());

        foreach ($credentials as $key => $value) {
            if (Str::contains($key, ['password', 'credential'])) {
                continue;
            }

            // Compatible with default of Laravel
            if (strcasecmp($key, 'email') === 0) {
                $key = 'identifier';
            }

            if (is_array($value) || $value instanceof Arrayable) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        if (!($userIdentify = $query->first())) {
            return;
        }

        if ($user = $userIdentify->user()->first()) {
            $user->setRelation('identify', $userIdentify);
        }

        return $user;
    }

    /**
     * Get the first key from the credential array.
     *
     * @param  array  $credentials
     * @return string|null
     */
    protected function firstCredentialKey(array $credentials)
    {
        foreach ($credentials as $key => $value) {
            return $key;
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        /** @var \Illuminate\Foundation\Auth\User $user */

        $plain = $credentials['password'] ?? $credentials['credential'];

        if (!$user->relationLoaded('identify')) {
            return false;
        }

        return $user->identify->check($plain, $this->hasher);

        // return $this->hasher->check($plain, $user->auth->getAuthPassword());
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
        $class = '\\' . ltrim($this->userModel, '\\');

        return new $class;
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createUserIdentifyModel()
    {
        $class = '\\' . ltrim($this->userIdentifyModel, '\\');

        return new $class;
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
        return $this->userModel;
    }

    /**
     * Sets the name of the Eloquent user model.
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->userModel = $model;

        return $this;
    }
}
