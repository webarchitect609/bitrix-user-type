<?php

use WebArch\BitrixUserPropertyType\Field\TimeField;

/**
 * For backward compatibility of the client code after moving TimeField
 */
class_alias(TimeField::class, "\\WebArch\\BitrixOrmTools\\Field\\TimeField");
