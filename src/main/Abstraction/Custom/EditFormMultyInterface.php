<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

/**
 * Interface EditFormMultyInterface
 * @package WebArch\BitrixUserPropertyType\Abstraction
 */
interface EditFormMultyInterface
{
    /**
     * Эта функция вызывается при выводе формы редактирования значения <b>множественного</b> свойства.
     *
     * <p>Если класс не предоставляет такую функцию,
     * то менеджер типов "соберет" требуемый html из вызовов GetEditFormHTML</p>
     * <p>Возвращает html для встраивания в ячейку таблицы.
     * в форму редактирования сущности (на вкладке "Доп. свойства")</p>
     * <p>Элементы $htmlControl приведены к html безопасному виду.</p>
     * <p>Поле VALUE $htmlControl - массив.</p>
     *
     * @param array $userField Массив описывающий поле.
     * @param array $htmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     *
     * @return string HTML для вывода.
     */
    public static function getEditFormHTMLMulty($userField, $htmlControl);
}
