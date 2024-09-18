<?php

namespace Chuoke\UserIdentify\Exceptions;

use RuntimeException;

class UserIdentifierExistsException extends RuntimeException
{
    public function __construct($type, $message = null)
    {
        parent::__construct(is_null($message) ? __("The user [{$type}] identifier already exists.") : $message);
    }
}
