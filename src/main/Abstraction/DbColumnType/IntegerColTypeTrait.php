<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\DbColumnType;

use CUserTypeInteger;

trait IntegerColTypeTrait
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
        return CUserTypeInteger::GetDBColumnType($userField);
    }
}
