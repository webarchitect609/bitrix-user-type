<?php

namespace WebArch\BitrixUserPropertyType\Abstraction;

abstract class UserTypeBase implements UserTypeInterface
{
    const BASE_TYPE_INT = 'int';

    const BASE_TYPE_DOUBLE = 'double';

    const BASE_TYPE_STRING = 'string';

    const BASE_TYPE_DATE = 'date';

    const BASE_TYPE_DATETIME = 'datetime';

    /**
     * @inheritdoc
     */
    public static function init()
    {
        AddEventHandler(
            'main',
            'OnUserTypeBuildList',
            [__CLASS__, 'getUserTypeDescription'],
            99999
        );
    }

    /**
     * @inheritdoc
     */
    public static function getUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => __CLASS__,
            "CLASS_NAME"   => __CLASS__,
            "DESCRIPTION"  => self::getDescription(),
            "BASE_TYPE"    => self::getBaseType(),
        ];
    }

}
