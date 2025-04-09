<?php

namespace PamungkasAndono\Nanamber;

use PamungkasAndono\Nanamber\Commands\NanamberCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NanamberServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('nanamber')
            ->hasConfigFile()
            ->hasMigration('create_nanamber_table')
            ->hasCommand(NanamberCommand::class);
    }
}
