<?php

namespace WebArch\BitrixUserPropertyType\Abstraction;

abstract class UserTypeBase implements UserTypeInterface
{
    /**
     * @inheritdoc
     */
    public static function init()
    {
        AddEventHandler(
            'main',
            'OnUserTypeBuildList',
            [static::class, 'getUserTypeDescription'],
            99999
        );
    }

    /**
     * @inheritdoc
     */
    public static function getUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => static::getUserTypeId(),
            "CLASS_NAME"   => static::class,
            "DESCRIPTION"  => static::getDescription(),
            "BASE_TYPE"    => static::getBaseType(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getUserTypeId()
    {
        return mb_substr(
            self::getClassNameWithoutNamespace(static::class) . md5(static::class),
            0,
            self::MAX_USER_TYPE_LEN
        );
    }

    /**
     * Возвращает имя класса без namespace
     *
     * @param string $className
     *
     * @return string
     *
     * TODO Вынести в пакет webarchitect609/php-tools , где будут общие утилиты.
     * TODO Разрешить передавать mixed - объект и брать его класс, или же полное имя класса.
     */
    private static function getClassNameWithoutNamespace($className)
    {
        $pos = mb_strrpos($className, '\\');
        if ($pos) {

            return mb_substr($className, $pos + 1);
        }

        return $pos;
    }

    /**
     * Возвращает массив, описывающий ошибку.
     *
     * @param array $userField Массив описывающий поле.
     * @param $errorMessage
     *
     * @return array [ 'id' => $userField['FIELD_NAME'], 'text' => $errorMessage]
     */
    protected static function createError($userField, $errorMessage)
    {
        if (!isset($userField['FIELD_NAME'])) {
            $userField['FIELD_NAME'] = null;
        }

        return [
            'id'   => $userField['FIELD_NAME'],
            'text' => trim($errorMessage),
        ];
    }

}
