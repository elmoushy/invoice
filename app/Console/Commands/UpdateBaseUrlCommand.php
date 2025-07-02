<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateBaseUrlCommand extends Command
{
    protected $signature = 'app:update-base-url {url : The new base URL}';
    protected $description = 'Update the base URL across all configuration files';

    public function handle()
    {
        $newUrl = $this->argument('url');

        // Remove trailing slash
        $newUrl = rtrim($newUrl, '/');

        // Update .env file
        $envPath = base_path('.env');
        if (File::exists($envPath)) {
            $envContent = File::get($envPath);
            $envContent = preg_replace('/^APP_URL=.*/m', "APP_URL={$newUrl}", $envContent);
            File::put($envPath, $envContent);
            $this->info("Updated APP_URL in .env file to: {$newUrl}");
        }

        // Clear config cache
        $this->call('config:clear');
        $this->call('config:cache');

        $this->info('Base URL updated successfully!');
        $this->info('Make sure to restart your development server if running.');

        return 0;
    }
}
