<?php

namespace DieterCoopman\DatabaseComparer;

use DieterCoopman\DatabaseComparer\Commands\DatabaseComparerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
