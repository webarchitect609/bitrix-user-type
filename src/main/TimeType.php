<?php

namespace WebArch\BitrixUserPropertyType;

use HtmlObject\Element;
use HtmlObject\Input;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\AdminListEditInterface;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\CheckableValueInterface;
use WebArch\BitrixUserPropertyType\Abstraction\Custom\ConvertibleValueInterface;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeBase;
use WebArch\BitrixUserPropertyType\Abstraction\UserTypeInterface;
use WebArch\BitrixUserPropertyType\Exception\LogicException;
use WebArch\BitrixUserPropertyType\Utils\HtmlHelper;

/**
 * Class TimeType
 *
 * TODO Дописать поддержку режима произвольного времени.
 *
 * @package WebArch\BitrixUserPropertyType
 */
class TimeType extends UserTypeBase implements ConvertibleValueInterface, CheckableValueInterface, AdminListEditInterface
{
    /**
     * Признак отрицательной величины. Может быть пустым(время положительное) или содержать знак "-"(время
     * отрицательное).
     */
    public const VALUE_NEGATIVE = 'negative';

    public const VALUE_HOUR = 'hour';

    public const VALUE_MINUTE = 'minute';

    public const VALUE_SECOND = 'second';

    private const S = ':';

    private const NUMBER_FORMAT = '%02d';

    private const SETTING_DEFAULT_VALUE = 'DEFAULT_VALUE';

    private const SETTING_IS_TIME_OF_DAY = 'IS_TIME_OF_DAY';

    private const SETTING_REQUIRED = 'REQUIRED';

    private const NEGATIVE_SIGN = '-';

    private const DAY_HOUR_MIN = 0;

    private const DAY_HOUR_MAX = 23;

    /**
     * @link https://dev.mysql.com/doc/refman/5.7/en/time.html
     */
    private const HOUR_MAX = 838;

    /**
     * Нельзя писать отрицательные часы, т.к. для этого есть специальный чекбокс.
     */
    private const HOUR_MIN = self::DAY_HOUR_MIN;

    /**
     * @var HtmlHelper
     */
    private static $htmlHelper;

    /**
     * @inheritDoc
     */
    public static function getDBColumnType($userField)
    {
        return 'TIME';
    }

    /**
     * @inheritDoc
     */
    public static function getBaseType()
    {
        return UserTypeInterface::BASE_TYPE_INT;
    }

    /**
     * @inheritDoc
     */
    public static function getDescription()
    {
        return 'Время(без даты)';
    }

    /**
     * @inheritDoc
     */
    public static function prepareSettings($userField)
    {
        $settings = $userField['SETTINGS'];
        if (!array_key_exists(self::SETTING_IS_TIME_OF_DAY, $settings)) {
            $settings[self::SETTING_IS_TIME_OF_DAY] = false;
        } else {
            $settings[self::SETTING_IS_TIME_OF_DAY] = (bool)$settings[self::SETTING_IS_TIME_OF_DAY];
        }
        if (!array_key_exists(self::VALUE_NEGATIVE, $settings[self::SETTING_DEFAULT_VALUE])) {
            /**
             * На всякий случай добавляется в начало.
             */
            $settings[self::SETTING_DEFAULT_VALUE] = [self::VALUE_NEGATIVE => '']
                + $settings[self::SETTING_DEFAULT_VALUE];

        }
        /**
         * Если выбрано "Время суток", не может быть отрицательного значения
         * и не может быть часа вне диапазона 0-23.
         */
        if (true == $settings[self::SETTING_IS_TIME_OF_DAY]) {
            $settings[self::SETTING_DEFAULT_VALUE][self::VALUE_NEGATIVE] = '';
            if ($settings[self::SETTING_DEFAULT_VALUE][self::VALUE_HOUR] < self::DAY_HOUR_MIN) {
                $settings[self::SETTING_DEFAULT_VALUE][self::VALUE_HOUR] = self::DAY_HOUR_MIN;
            }
            if ($settings[self::SETTING_DEFAULT_VALUE][self::VALUE_HOUR] > self::DAY_HOUR_MAX) {
                $settings[self::SETTING_DEFAULT_VALUE][self::VALUE_HOUR] = self::DAY_HOUR_MAX;
            }
        }
        if (!array_key_exists(self::SETTING_REQUIRED, $settings)) {
            $settings[self::SETTING_REQUIRED] = false;
        } else {
            $settings[self::SETTING_REQUIRED] = (bool)$settings[self::SETTING_REQUIRED];
        }

        return [
            self::SETTING_IS_TIME_OF_DAY => $settings[self::SETTING_IS_TIME_OF_DAY],
            self::SETTING_DEFAULT_VALUE  => $settings[self::SETTING_DEFAULT_VALUE],
            self::SETTING_REQUIRED       => $settings[self::SETTING_REQUIRED],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function getSettingsHTML($userField, $htmlControl, $isVarsFromForm)
    {
        if ($isVarsFromForm) {
            $defaultValue = $GLOBALS[$htmlControl['NAME']][self::SETTING_DEFAULT_VALUE];
            $isTimeOfDay = (bool)$GLOBALS[$htmlControl['NAME']][self::SETTING_IS_TIME_OF_DAY];
            $isRequired = (bool)$GLOBALS[$htmlControl['NAME']][self::SETTING_IS_TIME_OF_DAY];
            $isMandatory = array_key_exists('MANDATORY', $GLOBALS) && 'Y' === $GLOBALS['MANDATORY'];
        } else {
            $defaultValue = $userField['SETTINGS'][self::SETTING_DEFAULT_VALUE];
            $isTimeOfDay = (bool)$userField['SETTINGS'][self::SETTING_IS_TIME_OF_DAY];
            $isRequired = (bool)$userField['SETTINGS'][self::SETTING_REQUIRED];
            $isMandatory = 'Y' === $userField['MANDATORY'];
        }

        $userFieldClone = $userField;
        $userFieldClone['SETTINGS'][self::SETTING_DEFAULT_VALUE] = $defaultValue;
        /**
         * Форсировать режим указания произвольного времени, т.к. сделать зависимость от "Время суток" невозможно.
         */
        $html = '';
        $htmlHelper = self::getHtmlHelper();

        $userFieldClone['SETTINGS'][self::SETTING_IS_TIME_OF_DAY] = false;
        $htmlControlClone = $htmlControl;
        $htmlControlClone['NAME'] = 'SETTINGS[' . self::SETTING_DEFAULT_VALUE . ']';
        $html .= Element::create('tr')
                        ->appendChild(
                            Element::create('td', 'Значение по умолчанию:')
                        )
                        ->appendChild(
                            Element::create('td', self::getEditFormHTML($userFieldClone, $htmlControlClone))
                        );

        $hint = 'Ограничивает час от 00 до 23, запрещает отрицательное время.';
        $id = self::getUserTypeId() . self::SETTING_IS_TIME_OF_DAY;
        $html .= Element::create('tr')
                        ->appendChild(
                            Element::create('td')
                                   ->appendChild(
                                       Element::create(
                                           'label',
                                           'Время суток:',
                                           [
                                               'for'   => $id,
                                               'title' => $hint,
                                           ]
                                       )
                                   )
                        )
                        ->appendChild(
                            Element::create(
                                'td',
                                $htmlHelper->getInputTypeCheckbox(
                                    $htmlControl['NAME'] . '[' . self::SETTING_IS_TIME_OF_DAY . ']',
                                    $isTimeOfDay,
                                    $id
                                )
                            )
                        );

        if ($isMandatory) {
            $warningText = 'Снимите стандартный флаг "Обязательное" выше и используйте вместо него этот:';
            $html .= Element::create('tr')
                            ->appendChild(Element::create('td', '&nbsp;'))
                            ->appendChild(
                                Element::create('td')
                                       ->appendChild(
                                           Element::create(
                                               'span',
                                               $warningText,
                                               ['style' => 'font-weight: bold; color: red;']
                                           )
                                       )
                            );
        }

        $hint = <<<'TXT'
* - используйте этот флаг обязательности вместо стандартного из-за несовместимости с проверкой значения типа array на пустоту в ядре Битрикс.
TXT;
        $id = self::getUserTypeId() . self::SETTING_REQUIRED;
        $html .= Element::create('tr')
                        ->appendChild(
                            Element::create('td')
                                   ->appendChild(
                                       Element::create(
                                           'label',
                                           Element::create('b', 'Обязательное:'),
                                           [
                                               'for'   => $id,
                                               'title' => $hint,
                                           ]
                                       )
                                   )
                        )
                        ->appendChild(
                            Element::create(
                                'td',
                                $htmlHelper->getInputTypeCheckbox(
                                    $htmlControl['NAME'] . '[' . self::SETTING_REQUIRED . ']',
                                    $isRequired,
                                    $id
                                )
                            )
                        );

        return $html;
    }

    /**
     * @inheritDoc
     */
    public static function getAdminListViewHtml($userField, $htmlControl)
    {
        if (trim($htmlControl['VALUE']) == '') {
            return 'пусто';
        }

        return $htmlControl['VALUE'];
    }

    /**
     * @inheritDoc
     */
    public static function getEditFormHTML($userField, $htmlControl)
    {
        /**
         * Применять значение по умолчанию.
         */
        if (!array_key_exists('VALUE', $htmlControl) || !is_array($htmlControl['VALUE'])) {
            $htmlControl['VALUE'] = $userField['SETTINGS'][self::SETTING_DEFAULT_VALUE];
        }

        $timeOfDay = $userField['SETTINGS'][self::SETTING_IS_TIME_OF_DAY];
        $isChecked = $htmlControl['VALUE'][self::VALUE_NEGATIVE] === self::NEGATIVE_SIGN;

        $negativeHint = 'Признак отрицательного времени.';
        $inputAttr = [
            'title' => $negativeHint,
        ];
        if ($isChecked) {
            $inputAttr['checked'] = 'checked';
        }
        $labelAttr = [
            'title' => $negativeHint,
        ];
        if ($timeOfDay) {
            $labelAttr['style'] = 'display: none;';
        }
        $negative = Element::create(
            'label',
            '&mdash;' . Input::create(
                $timeOfDay ? 'hidden' : 'checkbox',
                $htmlControl['NAME'] . '[' . self::VALUE_NEGATIVE . ']',
                $timeOfDay ? '' : self::NEGATIVE_SIGN,
                $inputAttr
            ),
            $labelAttr
        );

        if (true === $userField['SETTINGS'][self::SETTING_IS_TIME_OF_DAY]) {
            $hour = self::getHourSelect($userField, $htmlControl);
        } else {
            $hour = Input::create(
                'text',
                $htmlControl['NAME'] . '[' . self::VALUE_HOUR . ']',
                $htmlControl['VALUE'][self::VALUE_HOUR],
                [
                    'size'        => 3,
                    'placeholder' => 'Час',
                ]
            );
        }

        return $negative
            . $hour
            . self::getMinuteSelect($userField, $htmlControl)
            . self::getSecondSelect($userField, $htmlControl);
    }

    /**
     * @inheritDoc
     */
    public static function getAdminListEditHTML($userField, $htmlControl)
    {
        $htmlControl['VALUE'] = self::onAfterFetch($userField, $htmlControl);

        return self::getEditFormHTML($userField, $htmlControl);
    }

    /**
     * @param array $userField
     * @param array $htmlControl
     *
     * @return string
     */
    private static function getHourSelect(array $userField, array $htmlControl): string
    {
        $htmlHelper = self::getHtmlHelper();

        return $htmlHelper->getSelect(
            $htmlControl['NAME'] . '[' . self::VALUE_HOUR . ']',
            $htmlHelper->addOptionTitle(
                'Час',
                $htmlHelper->getOptionListWithNumbers(
                    self::DAY_HOUR_MIN,
                    self::DAY_HOUR_MAX,
                    $htmlControl['VALUE'][self::VALUE_HOUR],
                    null,
                    1,
                    self::NUMBER_FORMAT
                )
            ),
            null,
            true === $userField['SETTINGS'][self::SETTING_REQUIRED]
        );
    }

    /**
     * @param array $userField
     * @param array $htmlControl
     *
     * @return string
     */
    private static function getMinuteSelect(array $userField, array $htmlControl)
    {
        $htmlHelper = self::getHtmlHelper();

        return $htmlHelper->getSelect(
            $htmlControl['NAME'] . '[' . self::VALUE_MINUTE . ']',
            $htmlHelper->addOptionTitle(
                'Минута',
                $htmlHelper->getOptionListWithNumbers(
                    0,
                    59,
                    $htmlControl['VALUE'][self::VALUE_MINUTE],
                    null,
                    1,
                    self::NUMBER_FORMAT
                )
            ),
            null,
            true === $userField['SETTINGS'][self::SETTING_REQUIRED]
        );
    }

    /**
     * @param array $userField
     * @param array $htmlControl
     *
     * @return string
     */
    private static function getSecondSelect(array $userField, array $htmlControl)
    {
        $htmlHelper = self::getHtmlHelper();

        return $htmlHelper->getSelect(
            $htmlControl['NAME'] . '[' . self::VALUE_SECOND . ']',
            $htmlHelper->addOptionTitle(
                'Секунда',
                $htmlHelper->getOptionListWithNumbers(
                    0,
                    59,
                    $htmlControl['VALUE'][self::VALUE_SECOND],
                    null,
                    1,
                    self::NUMBER_FORMAT
                )
            ),
            null,
            true === $userField['SETTINGS'][self::SETTING_REQUIRED]
        );
    }

    /**
     * @return HtmlHelper
     */
    private static function getHtmlHelper(): HtmlHelper
    {
        if (is_null(self::$htmlHelper)) {
            self::$htmlHelper = new HtmlHelper();
        }

        return self::$htmlHelper;
    }

    /**
     * @inheritDoc
     */
    public static function onAfterFetch($userField, $rawValue)
    {
        if (is_null($rawValue['VALUE']) || false === $rawValue['VALUE'] || trim($rawValue['VALUE']) == '') {
            return self::createValue(null, null, null, false);
        } elseif (is_string($rawValue['VALUE'])) {
            [$hour, $minute, $second] = explode(self::S, $rawValue['VALUE']);

            return self::createValue(abs($hour), $minute, $second, $hour < 0);
        }

        throw new LogicException(
            sprintf(
                'Unexpected value "%s" for field %s',
                $rawValue['VALUE'],
                $userField['FIELD_NAME']
            )
        );
    }

    /**
     * @inheritDoc
     */
    public static function onBeforeSave($userField, $value)
    {
        if (count(self::checkFields($userField, $value)) > 0) {
            return null;
        }

        return self::toString($value);
    }

    /**
     * @inheritDoc
     */
    public static function checkFields($userField, $value)
    {
        if (!array_key_exists(self::VALUE_NEGATIVE, $value)) {
            /**
             * Обязательно добавляется в начало.
             */
            $value = [self::VALUE_NEGATIVE => ''] + $value;
        }

        if (
            !is_array($value)
            || !isset(
                $value[self::VALUE_HOUR],
                $value[self::VALUE_MINUTE],
                $value[self::VALUE_SECOND]
            )
        ) {
            return [self::createError($userField, 'Неверная структура сохраняемого значения.')];
        }

        if (
            true === $userField['SETTINGS'][self::SETTING_REQUIRED]
            && (
                trim($value[self::VALUE_HOUR]) === ''
                || trim($value[self::VALUE_MINUTE]) === ''
                || trim($value[self::VALUE_SECOND]) === ''
            )
        ) {
            return [
                self::createError(
                    $userField,
                    sprintf(
                        'Поле %s обязательно к заполнению.',
                        $userField['FIELD_NAME']
                    )
                ),
            ];
        }

        if (false === $userField['SETTINGS'][self::SETTING_IS_TIME_OF_DAY]) {
            if ($value[self::VALUE_HOUR] < self::HOUR_MIN) {
                return [
                    self::createError(
                        $userField,
                        sprintf(
                            'Не вводите отрицательный час; используйте флаг "%s"',
                            self::NEGATIVE_SIGN
                        )
                    ),
                ];
            }
            if ($value[self::VALUE_HOUR] > self::HOUR_MAX) {
                return [
                    self::createError(
                        $userField,
                        sprintf(
                            'Час не может быть больше %d(это ограничение базы данных MySQL).',
                            self::HOUR_MAX
                        )
                    ),
                ];
            }
        }

        return [];
    }

    /**
     * @param null|int $hour
     * @param null|int $minute
     * @param null|int $second
     *
     * @param bool $negative
     *
     * @return int[]
     */
    public static function createValue(?int $hour, ?int $minute, ?int $second, bool $negative = false): array
    {
        return [
            self::VALUE_NEGATIVE => $negative ? self::NEGATIVE_SIGN : '',
            self::VALUE_HOUR     => is_null($hour) ? '' : sprintf(self::NUMBER_FORMAT, $hour),
            self::VALUE_MINUTE   => is_null($minute) ? '' : sprintf(self::NUMBER_FORMAT, $minute),
            self::VALUE_SECOND   => is_null($second) ? '' : sprintf(self::NUMBER_FORMAT, $second),
        ];
    }

    /**
     * @param mixed $value
     *
     * @return string|null
     */
    private static function toString($value): ?string
    {
        if (
            is_array($value)
            && array_key_exists(self::VALUE_NEGATIVE, $value)
            && trim($value[self::VALUE_HOUR]) != ''
            && trim($value[self::VALUE_MINUTE]) != ''
            && trim($value[self::VALUE_SECOND]) != ''
        ) {
            /**
             * Всеми другими методами должно гарантироваться, что соблюдается строгий порядок следования элементов:
             *  - negative
             *  - hour
             *  - minute
             *  - second
             */
            return array_shift($value) . implode(self::S, $value);
        }

        return null;
    }
}
