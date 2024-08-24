<?php

namespace Chuoke\UserIdentify\Datas;

use Spatie\DataTransferObject\DataTransferObject;

class UserIdentifierCreateData extends DataTransferObject
{
    public $type;

    public string $identifier;

    public string $credential;
}
