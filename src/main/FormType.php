<?php /** @noinspection PhpUnusedParameterInspection */

namespace WebArch\BitrixUserPropertyType;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CForm;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\AdminListEditMultyInterface;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\AdminListViewMultyInterface;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\EditFormMultyInterface;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\FilterInterface;
use WebArch\BitrixUserPropertyType\Abstraction\DbColumnType\StringColTypeTrait;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;

/**
 * Class BitrixFormType
 *
 * Привязка к форме
 *
 * @package WebArch\BitrixUserPropertyType
 */
class FormType extends UserTypeBase implements
    FilterInterface,
    AdminListEditMultyInterface,
    EditFormMultyInterface,
    AdminListViewMultyInterface
{
    use StringColTypeTrait;

    /**
     * @var array
     */
    private static $formsList;

    /**
     * @inheritDoc
     */
    public static function getBaseType()
    {
        return self::BASE_TYPE_STRING;
    }

    /**
     * @inheritDoc
     */
    public static function getDescription()
    {
        return 'Привязка к форме';
    }

    /**
     * @inheritDoc
     */
    public static function prepareSettings($userField)
    {
        return [
            'DEFAULT_VALUE' => $userField['SETTINGS']['DEFAULT_VALUE'] ?: '',
        ];
    }

    /**
     * @inheritDoc
     * @throws LoaderException
     */
    public static function getFilterHTML($userField, $htmlControl)
    {
        return self::getFormFieldHtml($htmlControl['NAME'], $htmlControl['VALUE']);
    }

    /**
     * @inheritDoc
     * @throws LoaderException
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        return self::getFormFieldHtml($htmlControl['NAME'], $htmlControl['VALUE']);
    }

    /**
     * @inheritDoc
     * @throws LoaderException
     */
    public static function getEditFormHTMLMulty($userField, $htmlControl)
    {
        return self::getFormFieldHtml($htmlControl['NAME'], $htmlControl['VALUE']);
    }

    /**
     * @inheritDoc
     * @throws LoaderException
     */
    public static function getAdminListViewHTML($userField, $htmlControl)
    {
        return self::getFormName($htmlControl['VALUE']);
    }

    /**
     * @inheritDoc
     * @throws LoaderException
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
     * @inheritDoc
     * @throws LoaderException
     */
    public static function getAdminListEditHTML($userField, $htmlControl)
    {
        return static::getEditFormHTML($userField, $htmlControl);
    }

    /**
     * @inheritDoc
     * @throws LoaderException
     */
    public static function getAdminListEditHTMLMulty($userField, $htmlControl)
    {
        return static::getEditFormHTMLMulty($userField, $htmlControl);
    }

    /**
     * @inheritDoc
     */
    public static function getSettingsHTML($userField, $htmlControl, $isVarsFromForm)
    {
        return '';
    }

    /**
     * @param string $inputName
     * @param string $selectedValue
     * @param bool $addEmpty
     *
     * @throws LoaderException
     * @return string
     */
    protected static function getFormFieldHtml($inputName, $selectedValue = '', $addEmpty = true)
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

            $input .= '<option '
                . ($selected ? 'selected' : '')
                . ' value="'
                . $item['SID']
                . '">'
                . $item['NAME']
                . '</option>';
        }
        $input .= '</select>';

        return $input;
    }

    /**
     * @param string $sid
     *
     * @throws LoaderException
     * @return string
     */
    protected static function getFormName($sid)
    {
        $sid = trim($sid);
        if ($sid == '') {
            return '';
        }

        $forms = self::getFormList();
        if (array_key_exists($sid, $forms) && array_key_exists('NAME', $forms[$sid])) {
            return trim($forms[$sid]['NAME']);
        }

        return trim($sid);
    }

    /**
     * @throws LoaderException
     * @return array
     */
    protected static function getFormList()
    {
        if (is_array(self::$formsList)) {
            return self::$formsList;
        }

        self::$formsList = [];
        if (Loader::includeModule('form')) {
            $by = 's_name';
            $order = 'asc';
            $isFiltered = null;

            $dbres = CForm::GetList($by, $order, [], $isFiltered);
            while ($item = $dbres->Fetch()) {
                if (!empty($item['SID'])) {
                    self::$formsList[$item['SID']] = $item;
                }
            }
        }

        return self::$formsList;
    }
}
