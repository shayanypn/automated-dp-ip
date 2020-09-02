<?php

namespace App\Jobs\SyncIP;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\SyncStatus;


class Fetch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const URL = "https://db-ip.com/account/adcda1fff413ac2395a751f7cb7fdd28706cc197/db/ip-to-location-isp/";

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');

        $this->print = new ConsoleOutput();
        $this->info('start fetching process');

        // Step 1
        $response = $this->initialFetch();
        $name = $response['csv']['name'];
        $url = $response['csv']['url'];
        $rows = $response['csv']['rows'];

        // Step 2
        $this->downloadFile($url, $name, $url);

        // Step 3
        $this->storeFile($url, $name, $rows);

        $this->info('start fetching process finished completely.');
    }

    private function initialFetch() {
        $this->info('Reading JSON file ...');
        $response = Http::get(self::URL);

        if (!$response->successful())
            throw new HttpException($response->status(), "Cannot read from " . self::URL);

        return $response->json();
    }

    private function downloadFile($url, $name, $rows) {
        $this->info("Start downloading file ...");

        $oldPercent = 0;
        $file = Http::withOptions([
            'progress' => function ($total, $downloaded) use (&$oldPercent) {
                // TODO implement progressbar API beside of current implementation
                $percent = 0;
                if ($downloaded)
                    $percent = (number_format(($downloaded / $total), 2) * 100);
                if ($oldPercent != $percent) {
                    $this->info("downloading: ".$percent."%");
                    $oldPercent = $percent;
                }
            }
        ])->get($url)->body();
        Storage::put($name, $file);
        $this->info("File completely downloaded.");
    }

    private function storeFile($url, $name, $rows) {
        DB::transaction(function () use ($name, $url, $rows) {
            SyncStatus::create([
                "type" => "ip",
                "file_name" => $name,
                "url" => $url,
                "rows" => $rows,
                "status" => SyncStatus::FETCHED,
                "stats_message" => ""
            ]);
        });
    }

    private function info($msg) {
        $this->print->writeln("SyncIP::Fetch -> ".$msg);
    }
}
