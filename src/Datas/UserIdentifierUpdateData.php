<?php

namespace Chuoke\UserIdentify\Datas;

class UserIdentifierUpdateData
{
    public string $identifier = '';

    public string $credential = '';

    public $passwordable;

    public $verified_at;

    public function __construct($args)
    {
        foreach ($args as $key => $val) {
            if (property_exists($this, $key)) {
                $this->{$key} = $val;
            }
        }
    }

    public function toArray()
    {
        return [
            'identifier' => $this->identifier,
            'credential' => $this->credential,
            'passwordable' => $this->passwordable,
            'verified_at' => $this->verified_at,
        ];
    }
}
