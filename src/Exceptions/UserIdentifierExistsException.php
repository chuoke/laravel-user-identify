<?php

namespace Chuoke\UserIdentify\Exceptions;

use RuntimeException;

class UserIdentifierExistsException extends RuntimeException
{
    public function __construct($message = null)
    {
        parent::__construct(is_null($message) ? __("The user identifier already exists.") : $message);
    }
}
