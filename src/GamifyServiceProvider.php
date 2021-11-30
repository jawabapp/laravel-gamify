<?php

namespace Jawabapp\Gamify;

use RegexIterator;
use RecursiveRegexIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Jawabapp\Gamify\Events\BadgeEarned;
use Jawabapp\Gamify\Listeners\SyncBadges;
use Jawabapp\Gamify\Console\MakeBadgeCommand;
use Jawabapp\Gamify\Console\MakePointCommand;
use Jawabapp\Gamify\Events\ReputationChanged;

class GamifyServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // publish config
        $this->publishes([
            __DIR__ . '/config/gamify.php' => config_path('gamify.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/config/gamify.php', 'gamify');

        // publish migration
        if (!class_exists('CreateGamifyTables')) {
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__ . '/migrations/create_gamify_tables.php.stub' => database_path("/migrations/{$timestamp}_create_gamify_tables.php"),
                __DIR__ . '/migrations/add_reputation_on_user_table.php.stub' => database_path("/migrations/{$timestamp}_add_reputation_field_on_user_table.php"),
            ], 'migrations');
        }

        // register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakePointCommand::class,
                MakeBadgeCommand::class,
            ]);
        }

        // register event listener
        Event::listen(ReputationChanged::class, SyncBadges::class);

        if (config('gamify.listeners.new_badge_earned')) {
            Event::listen(
                BadgeEarned::class,
                config('community.listeners.new_badge_earned')
            );
        }
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('badges', function () {
            return cache()->rememberForever('gamify.badges.all', function () {
                return $this->getBadges()->map(function ($badge) {
                    return new $badge;
                });
            });
        });
    }

    /**
     * Get all the badge inside app/Gamify/Badges folder
     *
     * @return Collection
     */
    protected function getBadges()
    {
        $badgeRootNamespace = config(
            'gamify.badge_namespace',
            $this->app->getNamespace() . 'Gamify\Badges'
        );

        $badges = [];

        // Get the first folder for the app. For the vast majority of all projects this is "App"
        $rootFolder = substr($badgeRootNamespace, 0, strpos($badgeRootNamespace, '\\'));

        // Create recursive searching classes
        $directory  = new RecursiveDirectoryIterator(app_path('Gamify/Badges/'));
        $iterator   = new RecursiveIteratorIterator($directory);
        $files      = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        // loop through each file found
        foreach ($files as $file) {

            // grab the directory for the file
            $fileDirectory =  pathinfo($file[0], PATHINFO_DIRNAME);

            //remove full server path and prepend the rootfolder 
            $fileDirectory = $rootFolder . str_ireplace(app_path(), '', $fileDirectory);

            // convert the forward slashes to backslashes
            $fileDirectory = str_ireplace('/', '\\', $fileDirectory);

            // get the file name
            $fileName = pathinfo($file[0], PATHINFO_FILENAME);

            //append namespace file path to the badges array to return
            $badges[] = $fileDirectory . "\\" . $fileName;
        }

        return collect($badges);
    }
}
