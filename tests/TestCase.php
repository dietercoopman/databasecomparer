<?php

namespace DieterCoopman\DatabaseComparer\Tests;

use DieterCoopman\DatabaseComparer\DatabaseComparerServiceProvider;
use DieterCoopman\DatabaseComparer\DatabaseManager;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'DieterCoopman\\DatabaseComparer\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
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
        config()->set('database.default', 'source');
        config()->set('database.connections.source', config()->get('database.connections.testing'));
        $migration = include __DIR__ . '/../database/migrations/create_databasecomparer_table.php.stub';
        $migration->up();

        config()->set('database.connections.target', config()->get('database.connections.sqlite'));
        config()->set('database.connections.target.database', ':memory:');

        $migration = include __DIR__ . '/../database/migrations/create_databasecomparer_other_table.php.stub';
        $migration->up();
    }

    public function test_schema_gets_fetched()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->useLaravalConnection();
        $schema = $databaseManager->getSchema('source');

        $this->assertInstanceOf(\Doctrine\DBAL\Schema\Schema::class, $schema);
    }

//    public function test_databases_are_the_same()
//    {
//        config()->set('database.connections.source', config()->get('database.connections.target'));
//
//        $databaseManager = app(DatabaseManager::class);
//        $databaseManager->useLaravalConnection();
//        $comparision = $databaseManager->compare();
//
//        $this->assertInstanceOf(DatabaseManager::class, $comparision);
//        $this->assertEquals(';', $comparision->getSql());
//    }

    public function test_sql_can_be_get()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->useLaravalConnection();

        $sql = $databaseManager->compare()->getSql();
        $this->assertIsString($sql);
    }

    public function test_databases_are_different()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->useLaravalConnection();

        $comparision = $databaseManager->compare();

        $this->assertInstanceOf(DatabaseManager::class, $comparision);
        $this->assertNotEquals(';', $comparision->getSql());
    }

    public function test_sqlfile_is_written()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->useLaravalConnection();

        $comparision = $databaseManager->compare();

        $comparision->saveToFile();
        $this->assertFileExists('database/comparison.sql');
        unlink('database/comparison.sql');
        $this->assertFileDoesNotExist('database/comparison.sql');
    }

    public function test_sql_contains_create_statement()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->useLaravalConnection();

        $sql = $databaseManager->compare()->getSql();

        $this->assertStringContainsString('CREATE', $sql);
    }

    public function test_sql_does_not_contain_create_statement()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->useLaravalConnection();

        $databaseManager->compare()->exec();

        $sql = $databaseManager->compare()->getSql();

        $this->assertStringNotContainsString('CREATE', $sql);
    }

    public function test_has_difference()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->useLaravalConnection();

        $this->assertTrue($databaseManager->compare()->hasDifference());
    }

    public function test_has_dropstatements()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->useLaravalConnection();

        $sql = $databaseManager->compare()->getSql();
        $this->assertStringContainsString('DROP TABLE', $sql);
    }
}
