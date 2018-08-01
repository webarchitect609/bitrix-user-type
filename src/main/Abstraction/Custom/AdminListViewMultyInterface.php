<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

/**
 * Interface AdminListViewMultyInterface
 * @package WebArch\BitrixUserPropertyType\Abstraction
 */
interface AdminListViewMultyInterface
{
    /**
     * Эта функция вызывается при выводе значения <b>множественного</b> свойства в списке элементов.
     *
     * <p>Возвращает html для встраивания в ячейку таблицы.</p>
     * <p>Если класс не предоставляет такую функцию,
     * то менеджер типов "соберет" требуемый html из вызовов GetAdminListViewHTML</p>
     * <p>Элементы $htmlControl приведены к html безопасному виду.</p>
     * <p>Поле VALUE $htmlControl - массив.</p>
     * @param array $userField Массив описывающий поле.
     * @param array $htmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     * @return string HTML для вывода.
     */
    public static function getAdminListViewHTMLMulty($userField, $htmlControl);
}
