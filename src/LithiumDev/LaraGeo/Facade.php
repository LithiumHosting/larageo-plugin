<?php

namespace LithiumDev\LaraGeo;


use Illuminate\Support\Facades\Facade as BaseFacade;

class Facade extends BaseFacade {
    protected static function getFacadeAccessor()
    {
        return 'larageo';
    }
}
