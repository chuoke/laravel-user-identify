<?php

namespace Chuoke\UserIdentify\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class UserIdentifier extends Model implements AuthenticatableContract
{
    use Authenticatable;

    protected $table = 'user_identifiers';

    protected $guarded = [];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relation\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            \config('user-identify.user_model'),
            \config('user-identify.table.foreign_key'),
            \config('user-identify.table.owner_key')
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
        switch ($this->type) {
            case 'username':
            case 'email':
            case 'mobile':
                return $hasher->check($plain, $this->credential);
        }

        return false;
    }
}
