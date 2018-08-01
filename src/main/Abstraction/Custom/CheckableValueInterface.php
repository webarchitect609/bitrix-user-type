<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

interface CheckableValueInterface
{
    /**
     * Эта функция валидатор.
     *
     * <p>Вызывается из метода CheckFields объекта $USER_FIELD_MANAGER.</p>
     * <p>Который в свою очередь может быть вызван из меторов Add/Update сущности владельца свойств.</p>
     *
     * @param array $userField Массив описывающий поле.
     * @param array $value значение для проверки на валидность
     *
     * @return array массив массивов ("id","text") ошибок. Если ошибок нет, должен возвращаться пустой массив.
     */
    public static function checkFields($userField, $value);
}
