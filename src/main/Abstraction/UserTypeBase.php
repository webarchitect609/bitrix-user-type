<?php

namespace WebArch\BitrixUserPropertyType\Abstraction;

abstract class UserTypeBase implements UserTypeInterface
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        AddEventHandler(
            'main',
            'OnUserTypeBuildList',
            [$this, 'getUserTypeDescription'],
            99999
        );
    }

    /**
     * @inheritdoc
     */
    public function getUserTypeDescription()
    {
        return [
            "USER_TYPE_ID" => $this->getUserTypeId(),
            "CLASS_NAME"   => static::class,
            "DESCRIPTION"  => $this->getDescription(),
            "BASE_TYPE"    => $this->getBaseType(),
        ];
    }

    /**
     * Возвращает уникальное имя типа на основе полного имени класса с учётом того, что длина не может превышать 50
     * символов.
     *
     * @return string
     */
    private function getUserTypeId()
    {
        return mb_substr($this->getClassName($this) . md5(static::class), 0, 50);
    }

    /**
     * Возвращает имя класса без namespace
     *
     * @param $object
     *
     * @return string
     *
     * TODO Вынести в пакет webarchitect609/php-tools , где будут общие утилиты.
     */
    private static function getClassName($object)
    {
        $className = get_class($object);
        $pos = strrpos($className, '\\');
        if ($pos) {

            return substr($className, $pos + 1);
        }

        return $pos;
    }

}
