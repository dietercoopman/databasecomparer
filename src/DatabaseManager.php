<?php

namespace DieterCoopman\DatabaseComparer;

use Illuminate\Support\Collection;
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

    public function getSql(): string
    {
        return $this->getStatements()->implode(';' . PHP_EOL) . ";";
    }

    public function exec(): void
    {
        $this->getStatements()->each(function ($sql) {
            DB::connection(config('databasecomparer.connections.target'))->statement($sql);
        });
    }

    public function saveToFile(): void
    {
        $file = fopen(config('databasecomparer.sqlfile'), "w") or die("Unable to open file!");
        fwrite($file, $this->getSql());
        fclose($file);
    }

    private function getStatements(): Collection
    {
        return collect($this->schemaDiff->toSaveSql(DB::connection(config('databasecomparer.connections.target'))->getDoctrineConnection()->getDatabasePlatform()));
    }

    /**
     * @param $sourceSchema <string>
     * @param $targetSchema <string>
     */
    private function getDifference($sourceSchema, $targetSchema): DatabaseManager
    {
        $comparator       = new \Doctrine\DBAL\Schema\Comparator();
        $this->schemaDiff = $comparator->compare($sourceSchema, $targetSchema);

        return $this;
    }

    public function hasDifference(): bool
    {
        return $this->getStatements()->count() ? true : false;
    }
}
