<?php

namespace LaravelGraphQL\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use LaravelGraphQL\GraphQL;use Illuminate\Support\Str;

class ClearGraphqlCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graphql:clear-cache';

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
        $this->info(Cache::has(GraphQL::CACHE_SCHEMAS));
        Cache::forget(GraphQL::CACHE_SCHEMAS);
        $this->info(Cache::get(GraphQL::CACHE_SCHEMAS));
        $this->info('The command was successful!');
    }
}
