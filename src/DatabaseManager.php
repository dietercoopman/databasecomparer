<?php

namespace DieterCoopman\DatabaseComparer;

use Doctrine\DBAL\DriverManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatabaseManager
{
    private $schemaDiff;
    private array $sourceConnectionData;
    private array $targetConnectionData;
    private bool  $usesLaravelConnection = false;

    public function useLaravalConnection(): void
    {
        $this->usesLaravelConnection = true;
    }

    public function getSchema($connectionOrSettings)
    {
        if ($this->usesLaravelConnection) {
            return DB::connection($connectionOrSettings)->getDoctrineConnection()->createSchemaManager()->createSchema();
        }

        return $this->getSchemaManager($connectionOrSettings)->createSchema();
    }

    private function getSchemaManager($connectionOrSettings)
    {
        return $this->getConnection($connectionOrSettings)->createSchemaManager();
    }

    private function getConnection($connectionOrSettings)
    {
        if ($this->usesLaravelConnection) {
            return DB::connection($connectionOrSettings)->getDoctrineConnection();
        }
        $connection = DriverManager::getConnection($this->getSettings($connectionOrSettings));
        $databasePlatform = $connection->getDatabasePlatform();
        $databasePlatform->registerDoctrineTypeMapping('enum', 'string');

        return $connection;
    }

    public function compare(): self
    {
        $sourceSchema = $this->getSchema('source');
        $targetSchema = $this->getSchema('target');

        return $this->getDifference($targetSchema, $sourceSchema);
    }

    public function getSql(): string
    {
        return $this->getStatements()->implode(';' . PHP_EOL) . ";";
    }

    public function exec(): void
    {
        $this->getStatements()->each(function ($sql) {
            $this->getConnection('target')->prepare($sql)->executeStatement();
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
        $statements = collect($this->schemaDiff->toSaveSql($this->getConnection('target')->getDatabasePlatform()));

        return $statements->merge($this->getDropStatements());
    }

    /**
     * @param $sourceSchema <string>
     * @param $targetSchema <string>
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    private function getDifference($targetSchema, $sourceSchema): DatabaseManager
    {
        $comparator = new \Doctrine\DBAL\Schema\Comparator();
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
        $sourceTables = $this->getTables($this->getSchema('source'));
        $targetTables = $this->getTables($this->getSchema('target'));
        $targetTables->diff($sourceTables)->each(function ($table) use (&$dropStatements) {
            $dropStatements->push("DROP TABLE $table");
        });

        return $dropStatements;
    }

    private function getTables($sourceSchema): Collection
    {
        return collect($sourceSchema->getTables())->transform(fn ($table) => $table->getName())->values();
    }

    private function getSettings(string $connection)
    {
        $settings = $this->{$connection . 'ConnectionData'};
        $settings['driver'] = "pdo_" . $settings['driver'];

        return $settings;
    }

    public function setSourceConnectionData($sourceConnectionData): void
    {
        $this->sourceConnectionData = $sourceConnectionData;
    }

    public function setTargetConnectionData($targetConnectionData): void
    {
        $this->targetConnectionData = $targetConnectionData;
    }
}
