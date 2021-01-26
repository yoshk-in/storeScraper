<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

class DBInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:init {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to create mysql Database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('next prompts set up mysql database configuration;
    if you use another db type you shoud remove "@php artisan db:init" cmd from post-create scripts in "composer.json" or deploy project manualy');

        $this->credentials();
        
        $schemaName = $this->argument('name') ??
            $this->askInput('database name', config('database.connections.mysql.database'));

        $charset = config("database.connections.mysql.charset",'utf8mb4');

        $collation = config("database.connections.mysql.collation",'utf8mb4_unicode_ci');

        config(["database.connections.mysql.database" => null]);

        $query = "CREATE DATABASE IF NOT EXISTS $schemaName CHARACTER SET $charset COLLATE $collation;";

        $success = (int) DB::statement($query);
        
        
        config(["database.connections.mysql.database" => $schemaName]);

        return $success;
    }

    private function credentials()
    {
        $userName = $this->askInput('db user name');
        $password = $this->secret('input db user password');
        config(["database.connections.mysql.username" => $userName]);
        config(["database.connections.mysql.password" => $password]);
    }

    private function askInput($msg, $default = null): string
    {
        return $this->ask('input ' . $msg . ' or press "enter to take value from config file', $default);
    }
}
