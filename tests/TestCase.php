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

        if(file_exists('./database/a.sqlite')){
            unlink('./database/a.sqlite');
        }
        touch('./database/a.sqlite');
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing.database', 'database/a.sqlite');
        $migration = include __DIR__ . '/../database/migrations/create_databasecomparer_table.php.stub';
        $migration->up();

        if(file_exists('./database/b.sqlite')){
            unlink('./database/b.sqlite');
        }
        touch('./database/b.sqlite');
        config()->set('database.connections.sqlite.database', 'database/b.sqlite');
        $migration = include __DIR__ . '/../database/migrations/create_databasecomparer_other_table.php.stub';
        $migration->up();

    }

    public function test_schema_gets_fetched()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $schema = $databaseManager->getSchema('source');
        $this->assertInstanceOf(\Doctrine\DBAL\Schema\Schema::class, $schema);
    }

    public function test_databases_are_the_same()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $databaseManager->setTargetConnectionData(config()->get('database.connections.testing'));
        $comparision = $databaseManager->compare();

        $this->assertInstanceOf(DatabaseManager::class, $comparision);
        $this->assertEquals(';', $comparision->getSql());
    }

    public function test_sql_can_be_get()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $databaseManager->setTargetConnectionData(config()->get('database.connections.sqlite'));

        $sql = $databaseManager->compare()->getSql();
        $this->assertIsString($sql);
    }

    public function test_databases_are_different()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $databaseManager->setTargetConnectionData(config()->get('database.connections.sqlite'));
        $comparision = $databaseManager->compare();

        $this->assertInstanceOf(DatabaseManager::class, $comparision);
        $this->assertNotEquals(';', $comparision->getSql());
    }

    public function test_sqlfile_is_written()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $databaseManager->setTargetConnectionData(config()->get('database.connections.sqlite'));
        $comparision = $databaseManager->compare();

        $comparision->saveToFile();
        $this->assertFileExists('database/comparison.sql');
        unlink('database/comparison.sql');
        $this->assertFileDoesNotExist('database/comparison.sql');
    }

    public function test_sql_contains_create_statement()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $databaseManager->setTargetConnectionData(config()->get('database.connections.sqlite'));
        $sql = $databaseManager->compare()->getSql();

        $this->assertStringContainsString('CREATE', $sql);
    }

    public function test_sql_does_not_contain_create_statement()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $databaseManager->setTargetConnectionData(config()->get('database.connections.sqlite'));
        $databaseManager->compare()->exec();

        $sql = $databaseManager->compare()->getSql();

        $this->assertStringNotContainsString('CREATE', $sql);
    }

    public function test_has_difference()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $databaseManager->setTargetConnectionData(config()->get('database.connections.sqlite'));

        $this->assertTrue($databaseManager->compare()->hasDifference());
    }

    public function test_has_dropstatements()
    {
        $databaseManager = app(DatabaseManager::class);
        $databaseManager->setSourceConnectionData(config()->get('database.connections.testing'));
        $databaseManager->setTargetConnectionData(config()->get('database.connections.sqlite'));

        $sql = $databaseManager->compare()->getSql();
        $this->assertStringContainsString('DROP TABLE', $sql);
    }
}
