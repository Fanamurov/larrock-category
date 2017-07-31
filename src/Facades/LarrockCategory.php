<?php

namespace Larrock\ComponentCategory\Facades;

use Illuminate\Support\Facades\Facade;

class LarrockCategory extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'larrockcategory';
    }

}