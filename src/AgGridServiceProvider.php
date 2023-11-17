<?php

namespace Clickbar\AgGrid;

use Clickbar\AgGrid\Console\Commands\MakeAgGridControllerCommand;
use Clickbar\AgGrid\Routing\PendingAgGridRegistration;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AgGridServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('ag-grid-laravel')
            ->hasConfigFile('ag-grid')
            ->hasCommand(MakeAgGridControllerCommand::class);
    }

    public function boot()
    {
        Route::macro('agGrid', function (string $route, string $controller) {
            return new PendingAgGridRegistration($route, $controller);
        });

        return parent::boot();
    }
}
