<?php

use WebArch\BitrixUserPropertyType\Field\TimeField;

/**
 * For backward compatibility of the client code after moving TimeField
 */
if (
    array_key_exists('DOCUMENT_ROOT', $_SERVER)
    && is_file($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php')
    && !class_exists("\\WebArch\\BitrixOrmTools\\Field\\TimeField")
) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php';
    class_alias(TimeField::class, "\\WebArch\\BitrixOrmTools\\Field\\TimeField");
}
