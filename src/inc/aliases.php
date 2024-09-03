<?php

use WebArch\BitrixUserPropertyType\Field\TimeField;

/**
 * For backward compatibility of the client code after moving TimeField.
 * require_once this file in init.php if you get 'Class "WebArch\BitrixOrmTools\Field\TimeField" not found' error 
 */
/** @noinspection ClassConstantCanBeUsedInspection */
if (!class_exists("\\WebArch\\BitrixOrmTools\\Field\\TimeField")) {
    class_alias(TimeField::class, "\\WebArch\\BitrixOrmTools\\Field\\TimeField");
}
