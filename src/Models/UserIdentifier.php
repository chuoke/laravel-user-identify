<?php

namespace Chuoke\UserIdentify\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $type
 * @property string $identifier
 * @property string $credential
 * @property string $credential
 * @property bool $passwordable
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $verified_at
 *
 * @property \Illuminate\Database\Eloquent\Model|null $user
 */
class UserIdentifier extends Model
{
    protected $table = 'user_identifiers';

    protected $guarded = [];

    protected function casts()
    {
        return [
            'last_used_at' => 'datetime',
            'verified_at' => 'datetime',
            'passwordable' => 'boolean',
        ];
    }

    public static function associateUserKey()
    {
        return config('user-identify.idetifier_user_key');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            \config('user-identify.user_model'),
            static::associateUserKey(),
            \config('user-identify.user_key')
        );
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  mixed  $plain
     * @param  \Illuminate\Contracts\Hashing\Hasher|null  $hasher
     * @return bool
     */
    public function check($plain, $hasher = null)
    {
        if ($this->passwordable && $plain) {
            return $hasher->check($plain, $this->credential);
        }

        return ! $this->passwordable;
    }
}
