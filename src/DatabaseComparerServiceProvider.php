<?php

namespace DieterCoopman\DatabaseComparer;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use DieterCoopman\DatabaseComparer\Commands\DatabaseComparerCommand;

class DatabaseComparerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('databasecomparer')
            ->hasConfigFile('databasecomparer')
            ->hasCommand(DatabaseComparerCommand::class);
    }
}
