<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\DbColumnType;

use CUserTypeString;

trait StringColTypeTrait
{
    /**
     * @param array $userField
     *
     * @return string
     *
     * @see UserTypeInterface::getDBColumnType
     */
    public static function getDBColumnType($userField)
    {
        /** @noinspection PhpDynamicAsStaticMethodCallInspection */
        return CUserTypeString::GetDBColumnType($userField);
    }
}
