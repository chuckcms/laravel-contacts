<?php

namespace Chuckcms\Contacts;

use Chuckcms\Contacts\Contracts\Contact as ContactContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class ContactsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->doPublishing();

        $this->registerModelBindings();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/contacts.php',
            'contacts'
        );
    }

    public function doPublishing()
    {
        if (!function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen (credit: Spatie)
            return;
        }

        $this->publishes([
            __DIR__.'/../config/contacts.php' => config_path('contacts.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_contacts_tables.php.stub' => $this->getMigrationFileName('create_contacts_tables.php'),
        ], 'migrations');
    }

    public function registerModelBindings()
    {
        $config = $this->app->config['contacts.models'];

        $this->app->bind(ContactContract::class, $config['contact']);
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @return string
     */
    public function getMigrationFileName($migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $migrationFileName) {
                return $filesystem->glob($path.'*_'.$migrationFileName);
            })
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
