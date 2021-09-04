<?php

namespace DieterCoopman\DatabaseComparer\Commands;

use Illuminate\Support\Facades\DB;

class DatabaseManager
{
    private $schemaDiff;

    public function getSchema(string $connection)
    {
        return DB::connection($connection)->getDoctrineConnection()->getSchemaManager()->createSchema();
    }

    public function compare(): self
    {
        $sourceSchema = $this->getSchema(config('databasecomparer.connections.source'));
        $targetSchema = $this->getSchema(config('databasecomparer.connections.target'));

        return $this->getDifference($targetSchema, $sourceSchema);
    }

    public function toSql($output = true)
    {
        $statements = $this->getStatements();
        if ($statements->count() > 0) {
            return $this->getStatements()->implode(';' . PHP_EOL).";";
        }
    }

    public function exec()
    {
        $this->getStatements()->each(function ($sql) {
            echo DB::connection(config('databasecomparer.connections.target'))->statement($sql);
        });
    }

    public function save()
    {
        $file = fopen("database/databasecomparer.sql", "w") or die("Unable to open file!");
        fwrite($file, $this->toSql());
        fclose($file);
    }

    private function getStatements()
    {
        return collect($this->schemaDiff->toSaveSql(DB::connection(config('databasecomparer.connections.target'))->getDoctrineConnection()->getDatabasePlatform()));
    }

    /**
     * @param $sourceSchema <string>
     * @param $targetSchema <string>
     * @return self
     */
    private function getDifference($sourceSchema, $targetSchema)
    {
        $comparator = new \Doctrine\DBAL\Schema\Comparator();
        $this->schemaDiff = $comparator->compare($sourceSchema, $targetSchema);

        return $this;
    }
}
