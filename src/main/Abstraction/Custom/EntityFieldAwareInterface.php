<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

use Bitrix\Main\ORM\Fields\Field;

interface EntityFieldAwareInterface
{
    /**
     * Этот метод вызывается при извлечении поля из базы данных и позволяет использовать поле из ORM D7 вместо сырого
     * значения.
     *
     * @param string $name
     * @param array $parameters @deprecated use configure* and add* methods instead
     *
     * @return Field
     */
    public static function getEntityField(string $name, array $parameters): Field;
}
