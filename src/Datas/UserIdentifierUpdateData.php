<?php

namespace Chuoke\UserIdentify\Datas;

use Spatie\DataTransferObject\DataTransferObject;

class UserIdentifierUpdateData extends DataTransferObject
{
    public string $identifier;

    public string $credential;
}
