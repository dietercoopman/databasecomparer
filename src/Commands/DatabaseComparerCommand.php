<?php

namespace DieterCoopman\DatabaseComparer\Commands;

use Illuminate\Console\Command;

class DatabaseComparerCommand extends Command
{
    public $signature = 'dbcomparer:compare
                        {--sql : show an sql statement as output}
                        {--migrations : create migrations for the comparison}
                        {--save : save the sql statements to an sql file}';

    public $description = 'This command syncs the structure of a target database with a source database';

    public function handle(DatabaseManager $databaseManager)
    {
        $comparison = $databaseManager->compare();
        $options = $this->options();

        if ($databaseManager->hasDifference()) {
            if ($options['save']) {
                $comparison->saveToFile();

                return $this->info('The sql statements are written to the file '.config('databasecomparer.sqlfile'));
            }

            if ($options['sql']) {
                return $this->info($comparison->getSql());
            }

            if ($options['migrations']) {
                return $comparison->saveToMigrations();
            }

            if ($this->confirm('Are you sure you want to sync your target database ?')) {
                return $comparison->exec();
            }
        } else {
            $this->info('There is no difference in the two compared databases.');
        }
    }
}
