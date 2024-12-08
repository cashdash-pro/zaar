<?php

namespace CashDash\Zaar\Commands;

use Illuminate\Console\Command;

class LaravelShopifyCommand extends Command
{
    public $signature = 'laravel-shopify';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
