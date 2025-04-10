<?php

namespace PamungkasAndono\Laravel;

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
            ->hasConfigFile(['nanamber'])
            ->hasMigration('2025_10_04_000000_create_auto_numbers_table');
    }
}
