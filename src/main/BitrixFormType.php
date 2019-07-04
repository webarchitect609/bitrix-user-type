<?php

namespace WebArch\BitrixUserPropertyType;

use Bitrix\Main\Loader;
use CForm;
use WebArch\BitrixUserPropertyType\Abstraction\DbColumnType\StringColTypeTrait;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;

/**
 * Class BitrixFormType
 *
 * Привязка к форме
 *
 * @package WebArch\BitrixUserPropertyType
 */
class BitrixFormType extends UserTypeBase
{
    use StringColTypeTrait;
    /**
     * @var array
     */
    private static $formsCache;

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
        return 'Привязка к форме';
    }

    /**
     * @param array $userField
     *
     * @return array
     */
    public static function prepareSettings($userField)
    {
        return [
            'DEFAULT_VALUE' => $userField['SETTINGS']['DEFAULT_VALUE'] ?: '',
        ];
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getFilterHTML($userField, $htmlControl)
    {
        return self::getFormFieldHtml($htmlControl['NAME'], $htmlControl['VALUE']);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        return self::getFormFieldHtml($htmlControl['NAME'], $htmlControl['VALUE']);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public function getEditFormHTMLMulty($userField, $htmlControl)
    {
        return self::getFormFieldHtml($htmlControl['NAME'], $htmlControl['VALUE']);
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getAdminListViewHTML($userField, $htmlControl)
    {
        return self::getFormName($htmlControl['VALUE'], '&nbsp;');
    }

    /**
     * @param $userField
     * @param $htmlControl
     *
     * @return string
     */
    public static function getAdminListViewHTMLMulty($userField, $htmlControl)
    {
        if (!empty($htmlControl['VALUE']) && is_array($htmlControl['VALUE'])) {
            $arPrint = [];
            foreach ($htmlControl['VALUE'] as $val) {
                $arPrint[] = self::getFormName($val);
            }
            return implode(' / ', $arPrint);
        }
        return '&nbsp;';
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
     * @return mixed
     */
    public static function getAdminListEditHTMLMulty($userField, $htmlControl)
    {
        return static::getEditFormHTMLMulty($userField, $htmlControl);
    }

    /**
     * @inheritdoc
     */
    public static function getSettingsHTML($userField, $htmlControl, $isVarsFromForm)
    {
        return '';
    }

    protected function getFormFieldHtml($inputName, $selectedValue = '', $addEmpty = true)
    {
        $items = self::getFormList();

        $multiple = strpos($inputName, '[]');

        $input = '<select style="max-width:250px;" ' . ($multiple ? 'multiple' : '') . ' name="' . $inputName . '">';

        $input .= ($addEmpty) ? '<option value="">нет</option>' : '';

        foreach ($items as $item) {
            if ($multiple || is_array($selectedValue)) {
                $selected = in_array($item['SID'], $selectedValue);
            } else {
                $selected = ($item['SID'] == $selectedValue);
            }

            $input .= '<option ' . ($selected ? 'selected' : '') . ' value="' . $item['SID'] . '">' . $item['NAME'] . '</option>';
        }
        $input .= '</select>';
        return $input;
    }

    protected function getFormName($sid, $default = '')
    {
        if (!empty($sid)) {
            $forms = self::getFormList();
            return isset($forms[$sid]) ? $forms[$sid]['NAME'] : $default;
        }
        return $default;
    }

    protected function getFormList()
    {
        if (is_array(self::$formsCache)) {
            return self::$formsCache;
        }

        self::$formsCache = [];
        if (Loader::includeModule('form')) {
            $by = 's_name';
            $order = 'asc';
            $isFiltered = null;

            $dbres = CForm::GetList($by, $order, [], $isFiltered);
            while ($item = $dbres->Fetch()) {
                if (!empty($item['SID'])) {
                    self::$formsCache[$item['SID']] = $item;
                }
            }
        }

        return self::$formsCache;
    }
}
