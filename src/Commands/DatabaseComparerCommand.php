<?php

namespace DieterCoopman\DatabaseComparer\Commands;

use Doctrine\DBAL\Schema\Table;
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

        if ($options['save']) {
            return $comparison->save();
        }

        if ($options['sql']) {
            return $this->info($comparison->toSql() ?? 'No database difference in this comparison');
        }

        if ($this->confirm('Are you sure you want to sync your target database ?')) {
            return $comparison->exec();
        }
    }
}
