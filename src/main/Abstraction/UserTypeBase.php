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

}
