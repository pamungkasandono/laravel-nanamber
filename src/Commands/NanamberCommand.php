<?php

namespace PamungkasAndono\Nanamber\Commands;

use Illuminate\Console\Command;

class NanamberCommand extends Command
{
    public $signature = 'nanamber';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
