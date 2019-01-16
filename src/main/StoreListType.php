<?php

namespace WebArch\BitrixUserPropertyType;

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use WebArch\BitrixUserPropertyType\Abstraction\DbColumnType\StringColTypeTrait;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;

/**
 * Class StoreListType
 *
 * Пользовательский тип свойства. Реализует привязку к складу по его XML_ID.
 *
 * @package Adv\AdvApplication\UserProperty
 */
class StoreListType extends UserTypeBase
{
    use StringColTypeTrait;

    const USER_TYPE = 'catalog_store_list';

    /**
     * @var array
     */
    protected static $stores = [];

    /**
     * @throws LoaderException
     */
    public static function init()
    {
        if (Loader::includeModule('catalog') && Loader::includeModule('sale')) {
            parent::init();
        }
    }

    /**
     * @return string
     */
    public static function getBaseType()
    {
        return self::BASE_TYPE_STRING;
    }

    /**
     * @return string
     */
    public static function getDescription()
    {
        return 'Привязка к складу';
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @return string
     */
    public static function getUserTypeId()
    {
        return self::USER_TYPE;
    }

    /**
     * @param array $userField
     *
     * @return array
     */
    public static function prepareSettings($userField)
    {
        return [
            'DEFAULT_VALUE' => (int)$userField['SETTINGS']['DEFAULT_VALUE'] ?: '',
        ];
    }

    /**
     * @param array $userField Array containing parameters of the user field.
     * @param       $htmlControl
     * @param       $varsFromForm
     *
     * @return string
     */
    public static function getSettingsHTML($userField, $htmlControl, $varsFromForm)
    {
        $result = '';
        $value = '';
        if ($varsFromForm) {
            $value = $GLOBALS[$htmlControl['NAME']]['DEFAULT_VALUE'];
        } elseif (is_array($userField)) {
            $value = $userField['SETTINGS']['DEFAULT_VALUE'];
        }

        $result .= '
        <tr>
            <td>Значение по умолчанию:</td>
            <td>
                ' . $value . '
            </td>
        </tr>
		';

        return $result;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getFilterHTML($userField, $htmlControl)
    {
        return self::getStoreSelectHTML($htmlControl['NAME'], $userField['FIELD_NAME']);
    }

    /**
     * @param      $name
     * @param null $current
     *
     * @return string
     */
    protected static function getStoreSelectHTML($name, $current = null)
    {
        $stores = self::getStoreList();

        $return = '<select name="' . $name . '">';
        $return .= '<option></option>';
        foreach ($stores as $xmlId => $store) {
            $return .= '<option value="' . $xmlId . '" ' . ($xmlId == $current ? 'selected' : '')
                       . '>[' . $xmlId . ']' . $store['TITLE'] . '</option>';
        }
        $return .= '</select>';

        return $return;
    }

    /**
     * @return array
     */
    protected static function getStoreList()
    {
        if (empty(self::$stores)) {
            $stores = StoreTable::getList(
                [
                    'filter' => ['ACTIVE' => 'Y'],
                    'select' => [
                        'ID',
                        'TITLE',
                        'XML_ID'
                    ],
                ]
            );

            /** @noinspection PhpAssignmentInConditionInspection */
            while ($store = $stores->fetch()) {
                self::$stores[$store['XML_ID']] = $store;
            }
        }

        return self::$stores;
    }


    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getAdminListEditHTML($userField, $htmlControl)
    {
        return static::getEditFormHTML($userField, $htmlControl);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        $return = '&nbsp;';

        if ($userField['EDIT_IN_LIST'] === 'Y') {
            if ($userField['VALUE_ID'] < 1 && !empty($userField['SETTINGS']['DEFAULT_VALUE'])) {
                $htmlControl['VALUE'] = $userField['SETTINGS']['DEFAULT_VALUE'];
            }
            $return = self::getStoreSelectHTML($userField['FIELD_NAME'], $htmlControl['VALUE']);
        } elseif (!empty($htmlControl['VALUE'])) {
            $return = static::getAdminListViewHTML($userField, $htmlControl);
        }

        return $return;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getAdminListViewHTML($userField, $htmlControl)
    {
        if (!empty($htmlControl['VALUE'])) {
            return '[' . $htmlControl['VALUE'] . ']' . self::getStoreByXmlId($htmlControl['VALUE'])['TITLE'];
        }

        return '&nbsp;';
    }

    /**
     * @param $xmlId
     *
     * @return array
     */
    protected static function getStoreByXmlId($xmlId)
    {
        if (!self::$stores) {
            self::getStoreList();
        }

        return null === self::$stores[$xmlId] ? [] : self::$stores[$xmlId];
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return mixed
     */
    public static function getAdminListEditHTMLMulty($userField, $htmlControl)
    {
        return static::getEditFormHTMLMulty($userField, $htmlControl);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getEditFormHTMLMulty($userField, $htmlControl)
    {
        $return = '&nbsp;';
        if ($userField['EDIT_IN_LIST'] === 'Y') {
            $name = $userField['FIELD_NAME'];
            $return = '<table id="table_' . $name . '">';
            if (is_array($htmlControl['VALUE']) && !empty($htmlControl['VALUE'])) {
                foreach ($htmlControl['VALUE'] as $i => $val) {
                    $return .= '<tr><td>' . self::getStoreSelectHTML($name . '[' . $i . ']', $val) . '</td></tr>';
                }
            }
            $return .= '<tr><td>' . self::getStoreSelectHTML($userField['FIELD_NAME'] . '[]') . '</td></tr>';

            $return .= '
            <tr>
                <td>
                    <input type="button" value="Добавить" onclick="addNewRow(\'table_' . $name . '\', \'' . $name . '[]\')">
                </td>
            </tr>';

            $return .= '</table>';
        } elseif (!empty($htmlControl['VALUE'])) {
            $return = static::getAdminListViewHTMLMulty($userField, $htmlControl);
        }

        return $return;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getAdminListViewHTMLMulty($userField, $htmlControl)
    {
        if (!empty($htmlControl['VALUE'])) {
            $arPrint = [];

            if (is_array($htmlControl['VALUE']) && !empty($htmlControl['VALUE'])) {
                foreach ($htmlControl['VALUE'] as $val) {
                    if (!empty($val)) {
                        $arPrint[] = '[' . $val . ']' . self::getStoreByXmlId($val)['TITLE'];
                    }
                }
            }

            return implode(' / ', $arPrint);
        }

        return '&nbsp;';
    }

    /**
     * @param $userField
     *
     * @return string
     */
    public static function onSearchIndex($userField)
    {
        if (is_array($userField['VALUE'])) {
            return static::getAdminListViewHTMLMulty($userField, ['VALUE' => $userField['VALUE']]);
        }

        return static::getAdminListViewHTML($userField, ['VALUE' => $userField['VALUE']]);
    }
}
