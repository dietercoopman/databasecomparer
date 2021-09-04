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
        $statements = collect($this->schemaDiff->toSaveSql(DB::connection(config('databasecomparer.connections.target'))->getDoctrineConnection()->getDatabasePlatform()));
        return $statements->merge($this->getDropStatements());
    }

    /**
     * @param $sourceSchema <string>
     * @param $targetSchema <string>
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function getDifference($targetSchema, $sourceSchema): DatabaseManager
    {
        $comparator       = new \Doctrine\DBAL\Schema\Comparator();
        $this->schemaDiff = $comparator->compare($targetSchema, $sourceSchema);
        return $this;
    }

    public function hasDifference(): bool
    {
        return (bool)$this->getStatements()->count();
    }


    private function getDropStatements(): Collection
    {
        $dropStatements = collect();
        $sourceTables = $this->getTables($this->getSchema(config('databasecomparer.connections.source')));
        $targetTables = $this->getTables($this->getSchema(config('databasecomparer.connections.target')));
        $targetTables->diff($sourceTables)->each(function($table) use (&$dropStatements){
            $dropStatements->push("DROP TABLE $table");
        });
        return $dropStatements;
    }

    private function getTables($sourceSchema): Collection
    {
        return collect($sourceSchema->getTables())->transform(fn($table) => $table->getName())->values();
    }
}
