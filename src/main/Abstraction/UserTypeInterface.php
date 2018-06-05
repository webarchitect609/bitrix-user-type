<?php

namespace WebArch\BitrixUserPropertyType\Abstraction;

/**
 * Interface UserTypeInterface
 * @package WebArch\BitrixUserPropertyType\Abstraction
 *
 * @see \CUserTypeString для дополнительной справки, т.к. документации не существует
 * @see \CUserTypeInteger
 *
 */
interface UserTypeInterface
{
    /**
     * Инициализирует тип свойства, добавляя вызов getUserTypeDescription() при событии
     * main::OnUserTypeBuildList
     *
     * @return void
     */
    public static function init();

    /**
     * Возвращает базовый тип на котором будут основаны операции фильтра (int, double, string, date, datetime)
     *
     * @return string
     */
    public static function getBaseType();

    /**
     * Возвращает описание для показа в интерфейсе (выпадающий список и т.п.)
     *
     * @return string
     */
    public static function getDescription();

    /**
     * Обработчик события OnUserTypeBuildList.
     *
     * <p>Эта функция регистрируется в качестве обработчика события OnUserTypeBuildList.
     * Возвращает массив описывающий тип пользовательских свойств.</p>
     * <p>Элементы массива:</p>
     * <ul>
     * <li>USER_TYPE_ID - уникальный идентификатор
     * <li>CLASS_NAME - имя класса методы которого формируют поведение типа
     * <li>DESCRIPTION - описание для показа в интерфейсе (выпадающий список и т.п.)
     * <li>BASE_TYPE - базовый тип на котором будут основаны операции фильтра (int, double, string, date, datetime)
     * </ul>
     *
     * @return array
     */
    public static function getUserTypeDescription();

    /**
     * Эта функция вызывается при выводе формы редактирования значения свойства.
     *
     * <p>Возвращает html для встраивания в ячейку таблицы.
     * в форму редактирования сущности (на вкладке "Доп. свойства")</p>
     * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
     *
     * @param array $arUserField Массив описывающий поле.
     * @param array $arHtmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     *
     * @return string HTML для вывода.
     */
    public static function getEditFormHTML($arUserField, $arHtmlControl);
}
