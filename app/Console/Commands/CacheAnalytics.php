<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Memcached;
class CacheAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:analytics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache analytics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $memcached = new Memcached();
        $memcached->addServer(env('MEMCACHED_HOST', '127.0.0.1'), env('MEMCACHED_PORT', 11211));

        $keys = $memcached->getAllKeys();
        if (empty($keys)) {
            $this->info('No keys found in Memcached.');
            return 0;
        }

        $timestamp = Carbon::now()->timestamp;
        foreach ($keys as $key) {
            $value = $memcached->get($key);
            $valueOutput = is_scalar($value) ? $value : json_encode($value);

            if (is_numeric($valueOutput)) 
                $diff = $valueOutput - $timestamp;

            $this->line("Key: {$key}, Value: {$valueOutput}");
            if ($diff > 0)
                $this->line("Reset in: {$diff} seconds");
        }
        
        $this->line("Current time: {$timestamp}");
        return 0;
    }
}
