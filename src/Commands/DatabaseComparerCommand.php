<?php

namespace DieterCoopman\DatabaseComparer\Commands;

use DieterCoopman\DatabaseComparer\DatabaseManager;
use Illuminate\Console\Command;

class DatabaseComparerCommand extends Command
{
    public $signature = 'dbcomparer:compare
                        {--sql : show an sql statement as output}
                        {--save : save the sql statements to an sql file}';

    public $description = 'This command syncs the structure of a target database with a source database';

    public function handle(DatabaseManager $databaseManager)
    {
        $comparison = $databaseManager->compare();
        $options = $this->options();

        if ($databaseManager->hasDifference()) {
            if ($options['save']) {
                $comparison->saveToFile();
                $this->info('The sql statements are written to the file ' . config('databasecomparer.sqlfile'));

                return;
            }

            if ($options['sql']) {
                $this->info($comparison->getSql());

                return;
            }

            if ($this->confirm('Are you sure you want to sync your target database ?')) {
                $comparison->exec();
            }
        } else {
            $this->info('There is no difference in the two compared databases.');
        }
    }
}
