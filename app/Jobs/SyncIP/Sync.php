<?php

namespace App\Jobs\SyncIP;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\DB;
use App\Jobs\SyncIP\HttpException;
use App\SyncStatus;
use App\Ip;

class Sync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $this->print = new ConsoleOutput();
        $this->fields = array(
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

        $this->info("Start syncing IPs ...");

        SyncStatus::where("status", SyncStatus::UNZIPPED)->get()->each(function ($item) {
            try {
                $file_name = str_replace('.gz', '', $item->file_name);
                $file_path = storage_path('app/'.$file_name);
                $total = $item->rows;
                $count = 0;
                $oldPercent = 0;

                if (!is_file($file_path)){
                    // TODO throw error
                }

                $handle = fopen($file_path, 'r');
                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    $data = array_combine($this->fields, $row);
                    if (!Ip::where('ip_start', $data['ip_start'])->where('ip_end', $data['ip_end'])->first()) {
                        Ip::create($data);
                        $count++;

                        // TODO implement progressbar API beside of current implementation
                        $percent = (number_format(($count / $total), 2) * 100);
                        if ($percent !== $oldPercent) {
                            $oldPercent = $percent;
                            $this->info("syncing ".$percent."%");
                        }
                    }
                }
            } catch(\Exception $exception) {
                $this->info($exception->getMessage());
            }
        });

        $this->info("Sync done.");

    }
    private function info($msg) {
        $this->print->writeln("SyncIP::Sync -> ".$msg);
    }
}