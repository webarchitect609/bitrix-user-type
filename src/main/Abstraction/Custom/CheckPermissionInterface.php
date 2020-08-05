<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

interface CheckPermissionInterface
{
    /**
     * Проверяет, есть ли у пользователя $userId доступ к полю $userField.
     *
     * @param array $userField Массив описывающий поле.
     * @param int|false $userId Id пользователя.
     *
     * @return bool
     */
    public static function checkPermission(array $userField, $userId): bool;
}
