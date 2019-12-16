<?php

namespace Fundamental\Borica\Facades;

use Illuminate\Support\Facades\Facade;

class Borica extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'borica';
    }
}