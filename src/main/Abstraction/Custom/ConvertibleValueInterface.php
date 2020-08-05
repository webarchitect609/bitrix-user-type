<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

interface ConvertibleValueInterface
{
    /**
     * Эта функция вызывается перед сохранением значений в БД.
     *
     * <p>Вызывается из метода Update объекта $USER_FIELD_MANAGER.</p>
     * <p>Для множественных значений функция вызывается несколько раз.</p>
     *
     * @param array $userField Массив описывающий поле.
     * @param mixed $value Значение.
     *
     * @return string значение для вставки в БД.
     */
    public static function onBeforeSave($userField, $value);

    /**
     * Вызывается после извлечения значения из БД.
     *
     * @param array $userField Массив описывающий поле.
     * @param array $rawValue ['VALUE' => <актуальное значение>]
     *
     * @return null|mixed null для случая, когда значение поля не задано.
     */
    public static function onAfterFetch($userField, $rawValue);

    /**
     * Рекомендуется также определить публичный метод createValue, который позволит заполнять значение из php-кода.
     */
}
