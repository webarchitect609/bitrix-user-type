<?php

namespace WebArch\BitrixUserPropertyType\Abstraction;

/**
 * Interface UserTypeInterface
 *
 * Общие методы, которыми должен обладать класс своего типа пользовательского свойва.
 *
 *
 * @internal Все методы пришлось сделать статическими и работать только благодаря позднему статическому связыванию,
 *     т.к. Битрикс вызывает их строго статически. По-другому не получится.
 *
 * @package WebArch\BitrixUserPropertyType\Abstraction
 *
 * @see \CUserTypeString для дополнительной справки, т.к. документации не существует
 * @see \CUserTypeInteger
 *
 */
interface UserTypeInterface
{
    const BASE_TYPE_INT = 'int';

    const BASE_TYPE_DOUBLE = 'double';

    const BASE_TYPE_STRING = 'string';

    const BASE_TYPE_DATE = 'date';

    const BASE_TYPE_DATETIME = 'datetime';

    const BASE_TYPE_ENUM = 'enumeration';

    /**
     * Максимальная разрешённая длинна уникального идентификатора типа.
     */
    const MAX_USER_TYPE_LEN = 50;

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
     * Возвращает уникальное имя типа на основе полного имени класса с учётом того, что длина не может превышать
     * MAX_USER_TYPE_LEN символов.
     *
     * @return string
     */
    public static function getUserTypeId();

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
     * Возвращает тип столбца в базе данных для хранения значения и вызывается при добавлении нового свойства.
     *
     * <p>Эта функция вызывается для конструирования SQL запроса
     * создания колонки для хранения не множественных значений свойства.</p>
     * <p>Значения множественных свойств хранятся не в строках, а столбиках (как в инфоблоках)
     * и тип такого поля в БД всегда text.</p>
     *
     * @param array $userField Массив описывающий поле
     *
     * @return string
     *
     * @internal Метод обязательно должен быть статическим, т.к. в \CAllUserTypeManager::GetDBColumnType он вызывается
     *     только таким способом. При создании своих типов рекомендуется использовать готовые реализации из
     *     \CUserTypeInteger::GetDBColumnType , \CUserTypeString::GetDBColumnType и т.п.
     *
     * @see \CUserTypeInteger::GetDBColumnType
     * @see \CUserTypeString::GetDBColumnType
     */
    public static function getDBColumnType($userField);

    /**
     * Эта функция вызывается при выводе формы редактирования значения свойства.
     *
     * <p>Возвращает html для встраивания в ячейку таблицы.
     * в форму редактирования сущности (на вкладке "Доп. свойства")</p>
     * <p>Элементы $htmlControl приведены к html безопасному виду.</p>
     *
     * @param array $userField Массив описывающий поле.
     * @param array $htmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     *
     * @return string HTML для вывода.
     */
    public static function getEditFormHTML($userField, $htmlControl);

    /**
     * Эта функция вызывается при выводе значения свойства в списке элементов.
     *
     * <p>Возвращает html для встраивания в ячейку таблицы.</p>
     * <p>Элементы $arHtmlControl приведены к html безопасному виду.</p>
     *
     * @param array $userField Массив описывающий поле.
     * @param array $htmlControl Массив управления из формы. Содержит элементы NAME и VALUE.
     *
     * @return string HTML для вывода.
     */
    public static function getAdminListViewHtml($userField, $htmlControl);

    /**
     * Эта функция вызывается при выводе формы настройки свойства.
     *
     * <p>Возвращает html для встраивания в 2-х колоночную таблицу.
     * в форму usertype_edit.php</p>
     * <p>т.е. tr td bla-bla /td td edit-edit-edit /td /tr </p>
     *
     * @param array $userField
     * @param array $htmlControl
     * @param bool $isVarsFromForm
     *
     * @return string
     */
    public static function getSettingsHTML($userField, $htmlControl, $isVarsFromForm);

    /**
     * Эта функция вызывается перед сохранением метаданных свойства в БД.
     *
     * <p>Она должна "очистить" массив с настройками экземпляра типа свойства.
     * Для того что бы случайно/намеренно никто не записал туда всякой фигни.</p>
     *
     * @param array $userField Массив описывающий поле. <b>Внимание!</b> это описание поля еще не сохранено в БД!
     *
     * @return array Массив который в дальнейшем будет сериализован и сохранен в БД.
     */
    public static function prepareSettings($userField);
}
