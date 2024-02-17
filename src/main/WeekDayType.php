<?php

namespace WebArch\BitrixUserPropertyType;

use DateTime;
use Exception;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\AdminListViewMultyInterface;
use WebArch\BitrixUserPropertyType\Abstraction\DbColumnType\IntegerColTypeTrait;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;
use WebArch\BitrixUserPropertyType\Enum\WeekDayISO8601;
use function FormatDate;
use function in_array;
use function is_array;

/**
 * Class WeekDayType
 *
 * Пользовательский тип свойства. Позволяет реализовать привязку к дню недели.
 * Дни недели заводятся в формате ISO-8601 (1-7, с понедельника по воскресенье).
 *
 * @package WebArch\BitrixUserPropertyType
 */
class WeekDayType extends UserTypeBase implements AdminListViewMultyInterface
{
    use IntegerColTypeTrait;

    const USER_TYPE = 'week_day';

    /**
     * @var array
     */
    protected static $days = [];

    /**
     * @return string
     */
    public static function getBaseType()
    {
        return self::BASE_TYPE_INT;
    }

    /**
     * @return string
     */
    public static function getDescription()
    {
        return 'День недели';
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
     * @throws Exception
     * @return string
     *
     */
    public static function getFilterHTML($userField, $htmlControl)
    {
        return self::getSelectHTML($htmlControl['NAME'], $userField['FIELD_NAME']);
    }

    /**
     * @param      $name
     * @param null $current
     * @param bool $multiple
     *
     * @throws Exception
     * @return string
     *
     */
    protected static function getSelectHTML($name, $current = null, $multiple = false)
    {
        if (empty(static::$days)) {
            for ($i = WeekDayISO8601::MONDAY; $i <= WeekDayISO8601::SUNDAY; $i++) {
                static::$days[$i] = static::getDay($i);
            }
        }
        $return = '<select name="' . $name . '" ' . ($multiple ? 'multiple' : '') . '>';
        $return .= '<option></option>';
        foreach (static::$days as $i => $day) {
            $selected = false;
            if (null !== $current) {
                if (is_array($current)) {
                    /** @noinspection TypeUnsafeArraySearchInspection */
                    $selected = in_array($i, $current);
                } else {
                    $selected = $i === (int)$current;
                }
            }

            $return .= '<option value="' . $i . '" ' . ($selected ? 'selected' : '') . '>' . $day . '</option>';
        }
        $return .= '</select>';

        return $return;
    }

    /**
     * @param int $value
     *
     * @throws Exception
     * @return string
     *
     */
    protected static function getDay($value)
    {
        $dayOfWeek = date('N');

        return FormatDate('l', strtotime(($value - $dayOfWeek) . ' day', (new DateTime())->getTimestamp()));
    }

    /**
     *
     * @param $userField
     * @param $htmlControl
     *
     * @throws Exception
     * @return string
     */
    public static function getAdminListEditHTML($userField, $htmlControl)
    {
        return static::getEditFormHTML($userField, $htmlControl);
    }

    /**
     * @param array $userField
     * @param array $htmlControl
     *
     * @throws Exception
     * @return string
     *
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        $return = '&nbsp;';

        if ($userField['EDIT_IN_LIST'] === 'Y') {
            if ($userField['VALUE_ID'] < 1 && !empty($userField['SETTINGS']['DEFAULT_VALUE'])) {
                $htmlControl['VALUE'] = $userField['SETTINGS']['DEFAULT_VALUE'];
            }
            $return = self::getSelectHTML($userField['FIELD_NAME'], $htmlControl['VALUE']);
        } elseif (!empty($htmlControl['VALUE'])) {
            $return = static::getAdminListViewHTML($userField, $htmlControl);
        }

        return $return;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @throws Exception
     * @return string
     *
     */
    public static function getAdminListViewHTML($userField, $htmlControl)
    {
        if (!empty($htmlControl['VALUE'])) {
            return self::getDay($htmlControl['VALUE']);
        }

        return '&nbsp;';
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @throws Exception
     * @return string
     *
     */
    public static function getAdminListEditHTMLMulty($userField, $htmlControl)
    {
        return static::getEditFormHTMLMulty($userField, $htmlControl);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @throws Exception
     * @return string
     *
     */
    public static function getEditFormHTMLMulty($userField, $htmlControl)
    {
        $return = '&nbsp;';
        if ($userField['EDIT_IN_LIST'] === 'Y') {
            $return = '<table id="table_' . $userField['FIELD_NAME'] . '">
                <tr><td>' .
                    '<input type="hidden" name="' . $htmlControl['NAME'] . '" value="">' .
                    self::getSelectHTML($htmlControl['NAME'], $htmlControl['VALUE'], true) .
                '</td></tr>
            </table>';
        } elseif (!empty($htmlControl['VALUE'])) {
            $return = static::getAdminListViewHTMLMulty($userField, $htmlControl);
        }

        return $return;
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @throws Exception
     * @return string
     *
     */
    public static function getAdminListViewHTMLMulty($userField, $htmlControl)
    {
        if (!empty($htmlControl['VALUE'])) {
            $arPrint = [];

            if (is_array($htmlControl['VALUE']) && !empty($htmlControl['VALUE'])) {
                foreach ($htmlControl['VALUE'] as $val) {
                    if (!empty($val)) {
                        $arPrint[] = self::getDay($val);
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
     * @throws Exception
     * @return string
     *
     */
    public static function onSearchIndex($userField)
    {
        if (is_array($userField['VALUE'])) {
            return static::getAdminListViewHTMLMulty($userField, ['VALUE' => $userField['VALUE']]);
        }

        return static::getAdminListViewHTML($userField, ['VALUE' => $userField['VALUE']]);
    }
}
