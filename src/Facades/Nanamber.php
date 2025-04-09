<?php

namespace PamungkasAndono\Nanamber\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PamungkasAndono\Nanamber\Nanamber
 */
class Nanamber extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PamungkasAndono\Nanamber\Nanamber::class;
    }
}
