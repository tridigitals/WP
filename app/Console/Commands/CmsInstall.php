<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Exception;

class CmsInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:install 
                          {--fresh : Fresh installation (will clear existing data)} 
                          {--seed : Seed the database with sample data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure the CMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting CMS installation...');

        try {
            // Check requirements
            $this->checkRequirements();

            // Publish assets and config
            $this->publishAssets();

            // Configure database
            if ($this->configureDatabase()) {
                // Run migrations
                $this->runMigrations();

                // Create admin user
                $this->createAdminUser();

                // Set up initial settings
                $this->setupInitialSettings();

                // Generate storage link
                $this->createStorageLink();

                // Clear caches
                $this->clearCaches();

                // Optional: Seed sample data
                if ($this->option('seed')) {
                    $this->seedSampleData();
                }

                $this->info('CMS installation completed successfully!');
                $this->showNextSteps();
            }
        } catch (Exception $e) {
            $this->error('Installation failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Check system requirements.
     */
    protected function checkRequirements(): void
    {
        $this->info('Checking system requirements...');

        $requirements = [
            'PHP >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'BCMath PHP Extension' => extension_loaded('bcmath'),
            'Ctype PHP Extension' => extension_loaded('ctype'),
            'JSON PHP Extension' => extension_loaded('json'),
            'Mbstring PHP Extension' => extension_loaded('mbstring'),
            'OpenSSL PHP Extension' => extension_loaded('openssl'),
            'PDO PHP Extension' => extension_loaded('pdo'),
            'Tokenizer PHP Extension' => extension_loaded('tokenizer'),
            'XML PHP Extension' => extension_loaded('xml'),
            'GD PHP Extension' => extension_loaded('gd'),
            'Fileinfo PHP Extension' => extension_loaded('fileinfo'),
        ];

        $failed = false;
        foreach ($requirements as $requirement => $satisfied) {
            if (!$satisfied) {
                $this->error("Missing requirement: {$requirement}");
                $failed = true;
            }
        }

        if ($failed) {
            throw new Exception('Please install all required PHP extensions before continuing.');
        }

        $this->info('All requirements satisfied!');
    }

    /**
     * Publish required assets and configuration.
     */
    protected function publishAssets(): void
    {
        $this->info('Publishing assets and configuration...');

        $this->call('vendor:publish', [
            '--provider' => 'App\Providers\CmsServiceProvider',
            '--tag' => ['cms-config', 'cms-assets'],
            '--force' => true
        ]);
    }

    /**
     * Configure the database connection.
     */
    protected function configureDatabase(): bool
    {
        $this->info('Configuring database connection...');

        // Test database connection
        try {
            \DB::connection()->getPdo();
            $this->info('Database connection successful!');
            return true;
        } catch (\Exception $e) {
            $this->error('Could not connect to the database.');
            $this->error('Please check your database configuration in .env file.');
            $this->error($e->getMessage());
            
            if (!$this->confirm('Would you like to continue anyway?')) {
                return false;
            }
            return true;
        }
    }

    /**
     * Run database migrations.
     */
    protected function runMigrations(): void
    {
        $this->info('Running database migrations...');

        $command = $this->option('fresh') ? 'migrate:fresh' : 'migrate';
        
        $this->call($command);
    }

    /**
     * Create the admin user.
     */
    protected function createAdminUser(): void
    {
        $this->info('Creating admin user...');

        // Check if admin exists
        if (User::where('email', 'admin@example.com')->exists() && !$this->option('fresh')) {
            if (!$this->confirm('Admin user already exists. Do you want to create a new admin user?')) {
                return;
            }
        }

        $name = $this->ask('Enter admin name', 'Administrator');
        $email = $this->ask('Enter admin email', 'admin@example.com');
        $password = $this->secret('Enter admin password (min 8 characters)');

        while (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');
            $password = $this->secret('Enter admin password (min 8 characters)');
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info('Admin user created successfully!');
    }

    /**
     * Set up initial CMS settings.
     */
    protected function setupInitialSettings(): void
    {
        $this->info('Setting up initial configuration...');

        $siteName = $this->ask('Enter site name', config('app.name', 'Laravel CMS'));
        
        // Update config
        config(['app.name' => $siteName]);
        config(['cms.name' => $siteName]);

        // Save to settings
        $this->updateEnvFile('APP_NAME', $siteName);
    }

    /**
     * Create storage symbolic link.
     */
    protected function createStorageLink(): void
    {
        $this->info('Creating storage symbolic link...');
        
        $this->call('storage:link');
    }

    /**
     * Clear all caches.
     */
    protected function clearCaches(): void
    {
        $this->info('Clearing caches...');

        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('view:clear');
        $this->call('route:clear');
    }

    /**
     * Seed sample data if requested.
     */
    protected function seedSampleData(): void
    {
        $this->info('Seeding sample data...');

        $this->call('db:seed', [
            '--class' => 'CmsSeeder'
        ]);
    }

    /**
     * Show next steps after installation.
     */
    protected function showNextSteps(): void
    {
        $this->info('');
        $this->info('Next steps:');
        $this->info('1. Configure your web server to point to the public directory');
        $this->info('2. Set up your environment variables in .env file');
        $this->info('3. Configure mail settings');
        $this->info('4. Set up queue worker if needed: php artisan queue:work');
        $this->info('5. Visit /admin to access the admin panel');
        $this->info('');
    }

    /**
     * Update .env file.
     */
    protected function updateEnvFile($key, $value): void
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, preg_replace(
                "/^{$key}=.*/m",
                "{$key}=\"{$value}\"",
                file_get_contents($path)
            ));
        }
    }
}