<?php

namespace WebArch\BitrixUserPropertyType\Abstraction;

interface UserTypeInterface
{
    /**
     * Инициализирует тип свойства, добавляя вызов getUserTypeDescription() при событии
     * main::OnUserTypeBuildList
     *
     * @return void
     */
    public function init();


}
