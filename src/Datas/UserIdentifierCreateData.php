<?php

namespace Chuoke\UserIdentify\Datas;

class UserIdentifierCreateData
{
    public $type;

    public string $identifier;

    public string $credential;

    public function __construct($args)
    {
        foreach ($args as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }
}
