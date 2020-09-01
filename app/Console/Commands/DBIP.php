<?php

namespace App\Console\Commands;

use App\Ip;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

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

            // Step 1
            $this->info("step 1/4 \n");
            $response = $this->initialFetch();
            $name = $response['csv']['name'];
            $url = $response['csv']['url'];
            $total_rows = $url = $response['csv']['rows'];
            $out_file_name = str_replace('.gz', '', $name);

            // Step 2
            $this->info("step 2/4 \n");
            $this->downloadFile($url, $name);

            // Step 3
            $this->info("step 3/4 \n");
            $this->unZip($name, $out_file_name);

            // Step 4
            $this->info("step 4/4 \n");
            $this->insertData($out_file_name, $total_rows);
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());
        }
        return 0;
    }

    /**
     * Execute the console command.
     *
     */
    private function initialFetch() {
        $this->info('Reading JSON file ...');
        $response = Http::get(self::URL);

        if (!$response->successful())
            throw new HttpException($response->status(), "Cannot read from " . self::URL);

        $this->info('JSON file completely read ...');
        return $response->json();
    }


    /**
     * Execute the console command.
     *
     */
    private function downloadFile($url, $name) {
        $this->info("Start downloading file ...\r\n");
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
        ])->get($url)->body();
        Storage::put($name, $file);
        $this->info("\r\nFile completely downloaded.");
    }

    /**
     * Execute the console command.
     *
     */
    private function unZip($file_name, $out_file_name) {
        $this->info('Start unziping file ...');

        $buffer_size = 4096; // read 4kb at a time
        $file = gzopen(storage_path('app/'.$file_name), 'rb');
        $out_file = fopen(storage_path('app/'.$out_file_name), 'wb');

        while (!gzeof($file)) {
            fwrite($out_file, gzread($file, $buffer_size));
        }

        fclose($out_file);
        gzclose($file);
        $this->info('Unzip file completed.');
    }

    private function insertData($out_file_name, $total_rows) {
        $this->info("start insert to database...");

        $fields = array(
            "ip_start",
            "ip_end",
            "continent",
            "country",
            "stateprov",
            "district",
            "city",
            "zipcode",
            "latitude",
            "longitude",
            "geoname_id",
            "timezone_offset",
            "timezone_name",
            "weather_code",
            "isp_name",
            "as_number",
            "connection_type",
            "organization_name"
        );

        $bar = $this->output->createProgressBar($total_rows);
        $bar->start();

        $handle = fopen(storage_path('app/'.$out_file_name), 'r');
        while (($row = fgetcsv($handle, 1000, ',')) !== false) {
            $data = array_combine($fields, $row);
            if (!Ip::where('ip_start', $data['ip_start'])->where('ip_end', $data['ip_end'])->first()) {
                Ip::create($data);
            }
            $bar->advance();
            $progress->setProgress($downloaded);
        }
        $bar->finish();

        $this->info("completely inserted to database\n");
    }
}
