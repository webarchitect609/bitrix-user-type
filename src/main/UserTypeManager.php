<?php

namespace WebArch\BitrixUserPropertyType;

use WebArch\BitrixUserPropertyType\Abstraction\UserTypeInterface;

/**
 * Class UserTypeManager
 *
 * @package WebArch\BitrixUserPropertyType
 */
final class UserTypeManager
{
    private $classes = [];

    /**
     * UserPropertyManager constructor.
     *
     * @param array|string[] $propertyList
     */
    public function __construct(array $propertyList)
    {
        $this->classes = $propertyList;
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
                    'Class %s is not found or not implement WebArch\BitrixUserPropertyType\Abstraction\UserTypeInterface',
                    $className
                )
            );
        }
    }
}
