<?php

namespace App\Console\Commands;

use App\Ip;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;
use App\Jobs\SyncIP\Fetch;
use App\Jobs\SyncIP\Unzip;
use App\Jobs\SyncIP\Sync;

class DBIP extends Command
{
    public const URL = "https://db-ip.com/account/adcda1fff413ac2395a751f7cb7fdd28706cc197/db/ip-to-location-isp/";
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbip:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        try {
            \App\Jobs\SyncIP\Fetch::withChain([
                new Unzip,
                new Sync,
            ])->dispatch();
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
        return 0;
    }
}
