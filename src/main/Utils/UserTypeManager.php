<?php

namespace WebArch\BitrixUserPropertyType\Utils;

use WebArch\BitrixUserPropertyType\Abstraction\UserTypeInterface;
use WebArch\BitrixUserPropertyType\Exception\WrongUserTypeClassException;

/**
 * Class UserTypeManager
 *
 * @package WebArch\BitrixUserPropertyType
 */
class UserTypeManager
{
    private $classes = [];

    /**
     * UserPropertyManager constructor.
     *
     * @param array|string[] $propertyClassList
     */
    public function __construct(array $propertyClassList)
    {
        $this->classes = $propertyClassList;
    }

    /**
     * Init all properties
     */
    public function init()
    {
        foreach ($this->classes as $className) {
            if (class_exists($className)) {
                $property = new $className();

                if ($property instanceof UserTypeInterface) {
                    $property::init();

                    continue;
                }
            }

            throw new WrongUserTypeClassException(
                \sprintf(
                    'Class %s is not found or not implement %s',
                    $className,
                    UserTypeInterface::class
                )
            );
        }
    }
}
