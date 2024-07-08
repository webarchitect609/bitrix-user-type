<?php

namespace WebArch\BitrixUserPropertyType\Field;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ORM\Fields\ScalarField;
use WebArch\BitrixOrmTools\Field\Traits\UserFieldAwareTrait;
use WebArch\BitrixUserPropertyType\Exception\InvalidArgumentException;
use WebArch\BitrixUserPropertyType\TimeType;

class TimeField extends ScalarField
{
    use UserFieldAwareTrait {
        postInitialize as tryGetUserField;
    }

    /**
     * @var bool
     */
    private $timeOfDay = false;

    /**
     * @inheritDoc
     */
    public function __construct($name, $parameters = [])
    {
        parent::__construct($name, $parameters);
        $this->addFetchDataModifier([$this, 'cast']);
    }

    /**
     * @inheritDoc
     * @return null|array<string>
     */
    public function cast($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            if (TimeType::checkFields($this->getUserField(), $value) === []) {
                return $value;
            }

            /**
             * Invalid value.
             */
            return TimeType::createValue(0, 0, 0);
        }
        try {
            return TimeType::parseValue($value);
        } catch (InvalidArgumentException $exception) {
            /**
             * Invalid value.
             */
            return TimeType::createValue(0, 0, 0);
        }
    }

    /**
     * @inheritDoc
     */
    public function convertValueFromDb($value)
    {
        return TimeType::onAfterFetch($this->getUserField(), ['VALUE' => $value]);
    }

    /**
     * @inheritDoc
     */
    public function convertValueToDb($value)
    {
        return TimeType::onBeforeSave($this->getUserField(), $value);
    }

    /**
     * @inheritDoc
     */
    public function postInitialize()
    {
        $this->tryGetUserField();
        if (!$this->userFieldHasSettings()) {
            return null;
        }
        /**
         * @phpstan-ignore-next-line Метод userFieldHasSettings() уже проверил наличие ключа 'SETTINGS'.
         */
        $settings = $this->userField['SETTINGS'];
        if (
            array_key_exists(TimeType::SETTING_REQUIRED, $settings)
            && is_bool($settings[TimeType::SETTING_REQUIRED])
        ) {
            $this->configureRequired($settings[TimeType::SETTING_REQUIRED]);
        }
        if (
            array_key_exists(TimeType::SETTING_IS_TIME_OF_DAY, $settings)
            && is_bool($settings[TimeType::SETTING_IS_TIME_OF_DAY])
        ) {
            $this->setTimeOfDay($settings[TimeType::SETTING_IS_TIME_OF_DAY]);
        }
        if (
            array_key_exists(TimeType::SETTING_DEFAULT_VALUE, $settings)
            && is_array($settings[TimeType::SETTING_DEFAULT_VALUE])
        ) {
            $this->configureDefaultValue($settings[TimeType::SETTING_DEFAULT_VALUE]);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isTimeOfDay(): bool
    {
        return $this->timeOfDay;
    }

    /**
     * @param bool $timeOfDay
     *
     * @return $this
     */
    public function setTimeOfDay(bool $timeOfDay)
    {
        $this->timeOfDay = $timeOfDay;

        return $this;
    }

    /**
     * Возвращает поля пользовательского свойства, связанного с этим типом данных, чтобы все методы TimeType работали
     * без ошибок.
     *
     * @return array<string, mixed>
     */
    private function getUserField(): array
    {
        if (is_array($this->userField)) {
            return $this->userField;
        }

        return [
            'FIELD_NAME' => $this->getName(),
            'SETTINGS'   => [
                TimeType::SETTING_REQUIRED       => $this->isRequired(),
                TimeType::SETTING_IS_TIME_OF_DAY => $this->isTimeOfDay(),
                TimeType::SETTING_DEFAULT_VALUE  => $this->getDefaultValue(),
            ],
        ];
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public function isValueEmpty($value)
    {
        if ($value instanceof SqlExpression) {
            $value = $value->compile();
        }

        return is_null($value);
    }
}
