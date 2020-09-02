<?php

namespace App\Jobs\SyncIP;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Console\Output\ConsoleOutput;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\SyncStatus;

class Unzip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $this->print = new ConsoleOutput();
        $this->info("Start unziping file ...");

        SyncStatus::where('status', SyncStatus::FETCHED)->get()->each(function ($item) {
            try {   
                $file_path = storage_path('app/'.$item->file_name);

                if (is_file($file_path)){
                    $buffer_size = 4096; // read 4kb at a time
                    $file = gzopen(storage_path('app/'.$item->file_name), 'rb');
                    $out_file_name = str_replace('.gz', '', $item->file_name);
                    $out_file = fopen(storage_path('app/'.$out_file_name), 'wb');

                    while (!gzeof($file)) {
                        fwrite($out_file, gzread($file, $buffer_size));
                    }
                    fclose($out_file);
                    gzclose($file);

                    $item->status = SyncStatus::UNZIPPED;
                    $item->save();
                } else {
                    $item->status = SyncStatus::ERROR;
                    $item->status_message = "SyncIP::Unzip -> failed to find CSV file";
                    $item->save();
                }
            } catch(\Exception $exception) {
                $this->info($exception->getMessage());
            }
        });
        $this->info("unzip files done.");
    }
    private function info($msg) {
        $this->print->writeln("SyncIP::Unzip -> ".$msg);
    }
}