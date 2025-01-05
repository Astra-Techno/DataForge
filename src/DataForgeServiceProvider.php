<?php

namespace AstraTech\DataForge;

use Illuminate\Support\Facades\AliasLoader;
use Illuminate\Support\ServiceProvider;

class DataForgeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * This method is used to load any resources, such as routes,
     * configuration, views, migrations, etc., that your package
     * needs to register.
     *
     * @return void
     */
    public function boot()
    {
        // Load the package routes from the 'routes/api.php' file
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        
        $this->publishes([
            __DIR__ . '/../routes/' => base_path('routes/dataforge'),
        ], 'routes');
        
        // Example: You can add publishing of config files if necessary
        // $this->publishes([
        //     __DIR__ . '/../config/dataforge.php' => config_path('dataforge.php'),
        // ], 'config');
        
        // Ensure the app/DataForge directory exists
        $this->createDataForgeDirectory();
    }

    /**
     * Register any application services.
     *
     * This method is used to register bindings in the service container.
     * If you have classes or services that you want to bind to the Laravel container,
     * you can do that here.
     *
     * @return void
     */
    public function register()
    {
        // Example: Register a custom query handler if needed
        // $this->app->bind('CustomQueryHandler', function ($app) {
        //     return new CustomQueryHandler();
        // });
        
        // Bind the DataForge class to the container
        AliasLoader::getInstance()->alias('DataForge', \AstraTech\DataForge\Base\DataForge::class);
    }

    /**
     * Create the DataForge directory structure.
     */
    protected function createDataForgeDirectory()
    {
        $baseDirectory = app_path('DataForge');

        // Check if the directory already exists
        if (!File::exists($baseDirectory)) {
            // Create the directory structure
            File::makeDirectory($baseDirectory, 0755, true);
            
            // Optionally create subdirectories
            File::makeDirectory($baseDirectory . '/Sql', 0755, true);
            File::makeDirectory($baseDirectory . '/Entity', 0755, true);
            File::makeDirectory($baseDirectory . '/Task', 0755, true);
        }
    }
}
