<?php

namespace Modules\AgenticAI\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Modules\AgenticAI\Services\SchemaContextProvider;
use Modules\AgenticAI\Services\SemanticIntentRouter;

class AgenticAIServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'AgenticAI';

    protected string $moduleNameLower = 'agenticai';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        
        //Sanket v2.0 - register policy observer for auto-indexing into vector store
        $this->registerObservers();
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        
        //Sanket v2.0 - register SchemaContextProvider and SemanticIntentRouter as singletons
        $this->app->singleton(SchemaContextProvider::class);
        $this->app->singleton(SemanticIntentRouter::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \Modules\AgenticAI\Console\IngestPoliciesCommand::class,
            \Modules\AgenticAI\Console\IndexPoliciesCommand::class,
            \Modules\AgenticAI\Console\TestGeminiConnectionCommand::class,
            \Modules\AgenticAI\Console\TestOpenAIConnectionCommand::class,
            \Modules\AgenticAI\Console\WarmupAICommand::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'Resources/lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'Resources/lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.config('modules.paths.generator.component-class.path'));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }

    //Sanket v2.0 - register model observers for auto-indexing content into the vector store
    protected function registerObservers(): void
    {
        try {
            if (class_exists(\Modules\PolicySetting\Entities\PolicySettings::class)) {
                \Modules\PolicySetting\Entities\PolicySettings::observe(
                    \Modules\AgenticAI\Observers\PolicyAutoIndexObserver::class
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('AgenticAI: Failed to register policy observer', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
