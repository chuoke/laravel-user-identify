<?php

namespace Chuoke\UserIdentify\Traits;

trait CreateUserIdentifyModel
{
    /**
     * Create a new instance of the model.
     *
     * @return \Chuoke\UserIdentify\Models\UserIdentifier
     */
    protected function createUserIdentifyModel()
    {
        $userIdentifyModel = config('user-identify.idetifier_model');

        $class = '\\' . ltrim($userIdentifyModel, '\\');

        return new $class();
    }
}
