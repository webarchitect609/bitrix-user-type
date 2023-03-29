<?php

namespace WebArch\BitrixUserPropertyType\Abstraction;

trait CanUseArrayValueForSingleFieldTrait
{
    /**
     * @inheritDoc
     */
    public static function canUseArrayValueForSingleField(): bool
    {
        return true;
    }
}
