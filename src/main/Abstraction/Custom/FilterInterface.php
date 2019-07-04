<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

/**
 * Interface FilterInterface
 * @package WebArch\BitrixUserPropertyType\Abstraction\Custom
 */
interface FilterInterface
{
    /**
     * Эта функция вызывается при выводе фильтра на странице списка.
     *
     * <p>Возвращает html для встраивания в ячейку таблицы.</p>
     * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
     *
     * @param array $userField Массив описывающий поле.
     * @param array $htmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     *
     * @return string HTML для вывода.
     */
    public static function getFilterHtml($userField, $htmlControl);
}
