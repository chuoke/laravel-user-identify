<?php

namespace Chuoke\UserIdentify\Datas;

class UserIdentifierCreateData
{
    public $type;

    public string $identifier = '';

    public string $credential = '';

    public $verified_at;

    public $passwordable;

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
            'type' => $this->type,
            'identifier' => $this->identifier,
            'credential' => $this->credential,
            'passwordable' => $this->passwordable,
            'verified_at' => $this->verified_at,
        ];
    }
}
