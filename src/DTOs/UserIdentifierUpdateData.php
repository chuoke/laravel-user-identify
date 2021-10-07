<?php

namespace Chuoke\UserIdentify\DTOs;

use Spatie\DataTransferObject\DataTransferObject;

class UserIdentifierUpdateData extends DataTransferObject
{
    public string $identifier;

    public string $credential;
}
