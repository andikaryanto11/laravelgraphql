<?php

namespace LaravelGraphQL\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use LaravelGraphQL\GraphQL;
use Illuminate\Support\Str;

class ClearGraphqlSchemaCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graphql:clear-graphql-schema-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all graphql cache';

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
        $this->info('Clearing graphql cache');
        Cache::pull(GraphQL::CACHE_SCHEMAS);
        // Cache::flush();
        $this->info('The command was successful!');
    }
}
