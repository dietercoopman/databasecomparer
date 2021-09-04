<?php

namespace DieterCoopman\DatabaseComparer\Commands;

use Illuminate\Console\Command;

class DatabaseComparerCommand extends Command
{
    public $signature = 'skeleton';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
