<?php

use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;

class IblockElementLinkType extends UserTypeBase
{
    /**
     * @inheritdoc
     */
    public static function getBaseType()
    {
        self::BASE_TYPE_INT;
    }

    /**
     * @return string
     */
    public static function getDescription()
    {
        return 'Привязка к элементу инфоблока с окном поиска';
    }

    public static function getEditFormHTML($arUserField, $arHtmlControl)
    {
        return 'myHtml';
    }

}
