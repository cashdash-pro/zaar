<?php

namespace CashDash\Zaar\Concerns\Actions;

use Illuminate\Support\Fluent;

trait AsObject
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @see static::handle()
     */
    public static function run(mixed ...$arguments): mixed
    {
        return static::make()->handle(...$arguments);
    }

    public static function runIf(bool $boolean, mixed ...$arguments): mixed
    {
        // TODO: idk if this is right
        return $boolean ? static::make()->handle(...$arguments) : new Fluent;
    }

    public static function runUnless(bool $boolean, mixed ...$arguments): mixed
    {
        return static::make()->handleIf(! $boolean, ...$arguments);
    }
}
