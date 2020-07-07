<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

/**
 * Interface AdminListEditInterface
 * @package WebArch\BitrixUserPropertyType\Abstraction\Custom
 */
interface AdminListEditInterface
{
    /**
     * Возвращает форму редактирования значения в списке.
     *
     * @param array $userField Массив описывающий поле.
     * @param array $htmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     *
     * @return string HTML для вывода.
     */
    public static function getAdminListEditHTML($userField, $htmlControl);
}
