<?php

namespace Tswtools\Logx\Facades;

use Illuminate\Support\Facades\Facade;

class Logx extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'logx';
    }
}
