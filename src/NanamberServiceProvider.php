<?php

namespace PamungkasAndono\Nanamber;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use PamungkasAndono\Nanamber\Commands\NanamberCommand;

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
            ->hasViews()
            ->hasMigration('create_nanamber_table')
            ->hasCommand(NanamberCommand::class);
    }
}
