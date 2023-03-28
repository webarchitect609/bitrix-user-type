<?php

namespace WebArch\BitrixUserPropertyType\Abstraction\Custom;

interface ArrayForSingleValueAwareInterface
{
    /**
     * Определяет возможность использования массива в качестве значения немножественного свойства.
     *
     * @return bool
     */
    public static function canUseArrayValueForSingleField(): bool;
}
