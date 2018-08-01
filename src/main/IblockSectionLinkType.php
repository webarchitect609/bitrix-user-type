<?php

namespace WebArch\BitrixUserPropertyType;

use CIBlock;
use CIBlockSection;
use Exception;
use WebArch\BitrixCache\BitrixCache;
use WebArch\BitrixUserPropertyType\Abstraction\DbColumnType\IntegerColTypeTrait;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;

/**
 * Class IblockSectionLinkType
 *
 * Пользовательский тип свойства "Привязка к разделу инфоблока(с окном поиска)", который позволяет удобно указать
 * раздел, выбрав его из всплывающего окна.
 *
 * Призван заменить непригодный к нормальному использованию тип \CUserTypeIBlockSection "Привязка к разделам инф.
 * блоков" из-за того, что там разделы выводятся в виде сквозного списка по всему сайту. Однако, из-за того, что
 * используется стандартное всплывающее окно, нельзя сделать режим, когда можно привязать раздел любого инфоблока.
 * Поэтому при создании свойства должен быть заранее установлен инфоблок.
 *
 * @package WebArch\BitrixUserPropertyType
 */
class IblockSectionLinkType extends UserTypeBase
{
    use IntegerColTypeTrait;

    const LABEL_NO_VALUE = '(ничего не выбрано)';

    const SETTING_IBLOCK_ID = 'IBLOCK_ID';

    /**
     * @inheritdoc
     */
    public static function getBaseType()
    {
        return self::BASE_TYPE_INT;
    }

    /**
     * @inheritdoc
     */
    public static function getDescription()
    {
        return 'Привязка к разделу инфоблока(с окном поиска)';
    }

    /**
     * @inheritdoc
     */
    public static function getSettingsHTML($userField, $htmlControl, $isVarsFromForm)
    {
        $iblockOptions = self::getIblockOptionList($userField['SETTINGS'][self::SETTING_IBLOCK_ID]);

        $iblockIdSetting = self::SETTING_IBLOCK_ID;

        return <<<END
        <tr>
            <td>
                Инфоблок:
            </td>
            <td>
                <select name="{$htmlControl['NAME']}[{$iblockIdSetting}]" >{$iblockOptions}</select>
            </td>
        </tr>
END;

    }

    public static function prepareSettings($userField)
    {
        return [
            self::SETTING_IBLOCK_ID => (int)$userField['SETTINGS'][self::SETTING_IBLOCK_ID],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        $spanValue = self::getLinkedSectionFullName($htmlControl['VALUE']);

        //TODO Добавить множественный режим.
        $name = $htmlControl['NAME'];
        $key = 'x1';

        $spanId = 'sp_' . md5($name) . '_' . $key;

        $params = [
            'lang'      => LANGUAGE_ID,
            'IBLOCK_ID' => (int)$userField['SETTINGS'][self::SETTING_IBLOCK_ID],
            'n'         => $name,
            'k'         => $key,
            // 'iblockfix' => 'y',
        ];

        $popupWindowParams = '/bitrix/admin/iblock_section_search.php?' . htmlentities(http_build_query($params));

        $return = <<<END
            <input name="{$htmlControl['NAME']}"
                   id="{$name}[{$key}]"
                   value="{$htmlControl['VALUE']}"
                   size="5"
                   type="text">
            <input value="..." 
               onclick="jsUtils.OpenWindow('{$popupWindowParams}', 900, 700);" 
               type="button">&nbsp;
            <span id="{$spanId}">{$spanValue}</span>
END;

        return $return;
    }

    /**
     * @inheritdoc
     */
    public static function getAdminListViewHtml($userField, $htmlControl)
    {
        //TODO Добавить гиперссылку с ID раздела для перехода к его редактированию?
        $spanValue = self::getLinkedSectionFullName($htmlControl['VALUE']);

        return <<<END
        [{$htmlControl['VALUE']}]&nbsp;<span>{$spanValue}</span>
END;

    }

    /**
     * Возвращает полное имя привязанного раздела со всей иерархией.
     *
     * @param int $sectionId
     *
     * @return string
     * @throws Exception
     */
    private static function getLinkedSectionFullName($sectionId)
    {
        $bitrixCache = new BitrixCache();

        $doGetLinkedSectionFullName = function () use ($sectionId, $bitrixCache) {

            if ($sectionId <= 0) {
                $bitrixCache->abortCache();

                return self::LABEL_NO_VALUE;
            }

            $section = CIBlockSection::GetList([], ['=ID' => $sectionId], false, ['IBLOCK_ID'], ['nTopCount' => 1])
                                     ->Fetch();
            if (false == $section) {
                $bitrixCache->abortCache();

                return self::LABEL_NO_VALUE;
            }

            //TODO Придумать, как заставить работать такое тегирование
            // $bitrixCache->withIblockTag((int)$section['IBLOCK_ID']);

            $path = [];
            $dbChain = CIBlockSection::GetNavChain($section['IBLOCK_ID'], $sectionId, ['NAME']);
            while ($item = $dbChain->Fetch()) {
                $path[] = trim($item['NAME']);
            }

            return implode(' / ', $path);
        };

        $result = $bitrixCache->withId(__METHOD__ . '_' . $sectionId)
                              ->resultOf($doGetLinkedSectionFullName);

        return trim($result['result']);
    }

    /**
     * @param $currentValue
     *
     * @return string
     * @throws Exception
     */
    private static function getIblockOptionList($currentValue)
    {
        $html = '<option value="0" >(не выбран)</option>';

        foreach (self::getIblockList() as $id => $name) {
            /** @noinspection HtmlUnknownAttribute */
            $html .= sprintf(
                '<option value="%d" %s >%s</option>',
                $id,
                $currentValue == $id ? ' selected="selected" ' : '',
                $name
            );
        }

        return $html;
    }

    /**
     * @return array
     * @throws Exception
     */
    private static function getIblockList()
    {
        $doGetIblockList = function () {

            $iblockList = [];
            $dbIblockList = CIBlock::GetList();
            while ($iblock = $dbIblockList->Fetch()) {
                $iblockList[(int)$iblock['ID']] = sprintf(
                    '%s [%d]',
                    $iblock['NAME'],
                    $iblock['ID']
                );
            }

            return $iblockList;
        };

        return (new BitrixCache())->withId(__METHOD__)
                                  ->resultOf($doGetIblockList);
    }
}
