<?php

namespace PamungkasAndono\Laravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PamungkasAndono\Nanamber
 */
class Nanamber extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PamungkasAndono\Laravel\Nanamber::class;
    }
}
