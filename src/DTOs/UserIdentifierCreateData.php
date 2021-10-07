<?php

namespace Chuoke\UserIdentify\DTOs;

use Spatie\DataTransferObject\DataTransferObject;

class UserIdentifierCreateData extends DataTransferObject
{
    public $type;

    public string $identifier;

    public string $credential;
}
