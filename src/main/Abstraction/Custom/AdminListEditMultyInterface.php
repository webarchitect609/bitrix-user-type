<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

/**
 * Interface AdminListEditMultyInterface
 * @package WebArch\BitrixUserPropertyType\Abstraction
 */
interface AdminListEditMultyInterface
{
    /**
     * Эта функция вызывается при выводе <b>множественного</b> свойства в списке элементов в режиме
     * <b>редактирования</b>.
     *
     * <p>Возвращает html для встраивания в ячейку таблицы.</p>
     * <p>Если класс не предоставляет такую функцию,
     * то менеджер типов "соберет" требуемый html из вызовов GetAdminListEditHTML</p>
     * <p>Элементы $htmlControl приведены к html безопасному виду.</p>
     * <p>Поле VALUE $htmlControl - массив.</p>
     *
     * @param array $userField Массив описывающий поле.
     * @param array $htmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     *
     * @return string HTML для вывода.
     */
    public static function getAdminListEditHTMLMulty($userField, $htmlControl);
}
