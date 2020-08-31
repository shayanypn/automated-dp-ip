<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Http;

use Illuminate\Console\Command;

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
            ini_set('memory_limit', '-1');

            $this->info('Reading JSON file ...');
            $response = Http::get(self::URL);

            if (!$response->successful())
                throw new HttpException($response->status(), "Cannot read from " . self::URL);

            $response = $response->json();

            $this->info('Start downloading file ...');
            $progress = null;
            $io = $this->output;
            $file = Http::withOptions([
                'progress' => function ($total, $downloaded) use ($io, &$progress) {
                    if ($total > 0 && is_null($progress)) {
                        $progress = $io->createProgressBar($total);
                        $progress->start();
                    }

                    if (!is_null($progress)) {
                        if ($total === $downloaded) {
                            $progress->finish();
                            return;
                        }
                        $progress->setProgress($downloaded);
                    }
                }
            ])->get($response['csv']['url'])->body();
            Storage::put($response['csv']['name'], $file);


        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
        return 0;
    }
}
