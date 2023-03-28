<?php

namespace WebArch\BitrixUserPropertyType;

use WebArch\BitrixUserPropertyType\Abstraction\Custom\ArrayForSingleValueAwareInterface;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\CheckableValueInterface;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\ConvertibleValueInterface;
use WebArch\BitrixUserPropertyType\Abstraction\DbColumnType\StringColTypeTrait;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;

/**
 * Class HyperLinkType
 * @package WebArch\BitrixUserPropertyType
 *
 * TODO Реализовать и протестировать поддержку множественного режима
 * TODO Реализовать сортировку для множественного режима.
 */
class HyperLinkType extends UserTypeBase implements ConvertibleValueInterface, CheckableValueInterface, ArrayForSingleValueAwareInterface
{
    use StringColTypeTrait;

    const TARGET_SELF = '_self';

    const TARGET_BLANK = '_blank';

    const TARGET_PARENT = '_parent';

    const TARGET_TOP = '_top';

    const VALUE_TEXT = 'TEXT';

    const VALUE_HREF = 'HREF';

    const VALUE_TARGET = 'TARGET';

    const VALUE_SORT = 'SORT';

    /**
     * @inheritdoc
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
        return 'Гиперссылка';
    }

    /**
     * @inheritdoc
     */
    public static function getSettingsHTML($userField, $htmlControl, $isVarsFromForm)
    {
        return <<<END
        <tr>
            <td>&nbsp;</td>
            <td><p>Гипперссылка, которая редактируется в форме и в списке, но в списке выглядит как ссылка</p></td>
        </tr>
END;
    }

    /**
     * @inheritdoc
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        $valText = self::VALUE_TEXT;
        $valHref = self::VALUE_HREF;
        $valTarget = self::VALUE_TARGET;
        $valSort = self::VALUE_SORT;

        $targetOptionList = self::getTargetOptionList($htmlControl['VALUE'][self::VALUE_TARGET]);

        $html = <<<END
            <input type="text" 
                   name="{$htmlControl['NAME']}[{$valHref}]" 
                   value="{$htmlControl['VALUE'][self::VALUE_HREF]}" 
                   placeholder="Ссылка" 
                   size="60"><br>
            <input type="text" 
                   name="{$htmlControl['NAME']}[{$valText}]" 
                   value="{$htmlControl['VALUE'][self::VALUE_TEXT]}" 
                   placeholder="Текст" 
                   size="60"><br>
            <label>
                Открывать: 
                <select name="{$htmlControl['NAME']}[{$valTarget}]" >
                    {$targetOptionList}
                </select>
            </label>

END;

        if ('Y' == $userField['MULTIPLE']) {
            $html .= <<<END
                <label>
                    Сортировка: <input type="text" 
                                       name="{$htmlControl['NAME']}[{$valSort}]" 
                                       value="{$htmlControl['VALUE'][self::VALUE_SORT]}" 
                                       size="4" >
                </label>

END;

        } else {
            $html .= <<<END
                <input type="hidden" 
                       name="{$htmlControl['NAME']}[{$valSort}]" 
                       value="1" >

END;
        }

        return $html;

    }

    /**
     * @inheritdoc
     */
    public static function getAdminListViewHtml($userField, $htmlControl)
    {
        /**
         * Обязательно нужен html_entity_decode , т.к. иначе сериализованные данные будут повреждены!
         * Обязательно нужен ключ 'VALUE', т.к. данные передаются только в таком формате.
         */
        return self::renderHtml(self::onAfterFetch($userField, ['VALUE' => html_entity_decode($htmlControl['VALUE'])]));
    }

    public static function renderHtml($value)
    {
        if (count(self::checkFields([], $value))) {
            return '';
        }

        $text = $value[self::VALUE_TEXT];
        if (trim($text) == '') {
            $text = $value[self::VALUE_HREF];
        }

        /**
         * Важно, чтобы не было лишних пробелов и переносов строк впереди и позади ссылки,
         * т.к. это может быть встроено в вёрстку pixel-perfect.
         */
        return <<<END
<a href="{$value[self::VALUE_HREF]}" target="{$value[self::VALUE_TARGET]}" >{$text}</a>
END;

    }

    private static function getTargetOptionList($selectedTarget)
    {
        $targetList = [
            self::TARGET_SELF   => 'в том же окне',
            self::TARGET_BLANK  => 'в новом окне',
            self::TARGET_PARENT => 'во фрейме-родителе',
            self::TARGET_TOP    => 'в полном окне браузера без фреймов',
        ];

        $optionList = '';
        foreach ($targetList as $value => $name) {

            $selected = '';
            if ($value === $selectedTarget) {
                $selected = ' selected="selected" ';
            }

            $optionList .= <<<END
                <option {$selected} value="{$value}" >{$name}</option>
END;

        }

        return $optionList;
    }

    /**
     * @inheritdoc
     */
    public static function prepareSettings($userField)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function onBeforeSave($userField, $value)
    {
        if (count(self::checkFields($userField, $value)) > 0) {
            return '';
        }

        return serialize(
            self::createValue(
                $value[self::VALUE_HREF],
                $value[self::VALUE_TEXT],
                $value[self::VALUE_TARGET],
                $value[self::VALUE_SORT]
            )
        );
    }

    /**
     * @param string $href
     * @param string $text
     * @param string $target
     * @param int $sort
     *
     * @return array
     */
    public static function createValue($href, $text, $target, $sort)
    {
        return [
            self::VALUE_HREF   => trim($href),
            self::VALUE_TEXT   => trim($text),
            self::VALUE_TARGET => trim($target),
            self::VALUE_SORT   => (int)$sort,
        ];
    }

    /**
     * @inheritdoc
     * TODO Соединить onAfterFetch и onBeforeSave в трейт, реализующий ... по умолчанию? Но тогда надо объявлять
     *     createValue и checkFields
     */
    public static function onAfterFetch($userField, $rawValue)
    {
        if (!is_array($rawValue) || !isset($rawValue['VALUE'])) {
            return [];
        }

        return unserialize($rawValue['VALUE']);
    }

    /**
     * @inheritdoc
     */
    public static function checkFields($userField, $value)
    {
        if (
            !is_array($value)
            || !isset(
                $value[self::VALUE_HREF],
                $value[self::VALUE_TEXT],
                $value[self::VALUE_TARGET],
                $value[self::VALUE_SORT]
            )
        ) {
            return [self::createError($userField, 'Неверная структура сохраняемого значения')];
        }

        if ('Y' == $userField['MANDATORY'] && trim($value[self::VALUE_HREF]) == '') {
            return [self::createError($userField, 'Должна быть задана ссылка')];
        }

        if (trim($value[self::VALUE_SORT]) != '' && $value[self::VALUE_SORT] <= 0) {
            return [self::createError($userField, 'Сортировка должна быть натуральным числом.')];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public static function canUseArrayValueForSingleField(): bool
    {
        return true;
    }
}
