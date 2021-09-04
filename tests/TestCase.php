<?php

namespace DieterCoopman\DatabaseComparer\Tests;

use DieterCoopman\DatabaseComparer\DatabaseComparerServiceProvider;
use DieterCoopman\DatabaseComparer\DatabaseManager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'DieterCoopman\\DatabaseComparer\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            DatabaseComparerServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        $migration = include __DIR__ . '/../database/migrations/create_databasecomparer_table.php.stub';
        $migration->up();

        config()->set('database.connections.sqlite.database', ':memory:');
        $migration = include __DIR__ . '/../database/migrations/create_databasecomparer_other_table.php.stub';
        $migration->up();
    }

    public function test_schema_gets_fetched()
    {
        $databaseManager = app(DatabaseManager::class);
        $schema          = $databaseManager->getSchema('testing');
        $this->assertInstanceOf(\Doctrine\DBAL\Schema\Schema::class, $schema);
    }

    public function test_databases_are_the_same()
    {

        config()->set('databasecomparer.connections.source', 'testing');
        config()->set('databasecomparer.connections.target', 'testing');
        $databaseManager = app(DatabaseManager::class);
        $comparision     = $databaseManager->compare();

        $this->assertInstanceOf(DatabaseManager::class, $comparision);
        $this->assertEquals(';', $comparision->getSql());
    }

    public function test_sql_can_be_get()
    {
        config()->set('databasecomparer.connections.source', 'testing');
        config()->set('databasecomparer.connections.target', 'sqlite');
        $databaseManager = app(DatabaseManager::class);
        $sql             = $databaseManager->compare()->getSql();
        $this->assertIsString($sql);

    }

    public function test_databases_are_different()
    {
        config()->set('databasecomparer.connections.source', 'testing');
        config()->set('databasecomparer.connections.target', 'sqlite');
        $databaseManager = app(DatabaseManager::class);
        $comparision     = $databaseManager->compare();

        $this->assertInstanceOf(DatabaseManager::class, $comparision);
        $this->assertNotEquals(';', $comparision->getSql());
    }

    public function test_sqlfile_is_written()
    {
        config()->set('databasecomparer.connections.source', 'testing');
        config()->set('databasecomparer.connections.target', 'sqlite');
        $databaseManager = app(DatabaseManager::class);
        $comparision     = $databaseManager->compare();

        $comparision->saveToFile();
        $this->assertFileExists('database/comparison.sql');
        unlink('database/comparison.sql');
        $this->assertFileDoesNotExist('database/comparison.sql');
    }

    public function test_sql_contains_create_statement()
    {
        config()->set('databasecomparer.connections.source', 'testing');
        config()->set('databasecomparer.connections.target', 'sqlite');
        $databaseManager = app(DatabaseManager::class);
        $sql     = $databaseManager->compare()->getSql();

        $this->assertStringContainsString('CREATE',$sql);
    }

    public function test_sql_does_not_contain_create_statement()
    {
        config()->set('databasecomparer.connections.target', 'testing');
        config()->set('databasecomparer.connections.source', 'sqlite');
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->compare()->exec();

        $sql = $databaseManager->compare()->getSql();

        $this->assertStringNotContainsString('CREATE',$sql);
    }

    public function test_has_difference()
    {
        config()->set('databasecomparer.connections.source', 'testing');
        config()->set('databasecomparer.connections.target', 'sqlite');
        $databaseManager = app(DatabaseManager::class);

        $this->assertTrue($databaseManager->compare()->hasDifference());

    }
}
