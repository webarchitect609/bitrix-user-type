<?php

namespace WebArch\BitrixUserPropertyType\Exception;

use InvalidArgumentException as CommonInvalidArgumentException;

class InvalidArgumentException extends CommonInvalidArgumentException implements BitrixUserTypeExceptionInterface
{
}
