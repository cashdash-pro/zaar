<?php

namespace CashDash\Zaar\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \CashDash\Zaar\Zaar
 */
class Zaar extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \CashDash\Zaar\Zaar::class;
    }
}
