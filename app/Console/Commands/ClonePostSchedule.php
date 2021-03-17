<?php

namespace App\Console\Commands;

use App\Models\ProcessModel;
use Exception;
use Illuminate\Console\Command;

class ClonePostSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:clone_post {--ignore=} {--out=} {--limit=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule Clone Post description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected $out;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ignore = $this->option('ignore');
        $this->out = $this->option('out');
        $limit = $this->option('limit');

        $cm = "schedule:clone_post";

        $process_info = ProcessModel::runProcess($cm);

        if ($process_info && $ignore == 'process') {
            $process_info = null;
        }

        if ($process_info) {
            $this->printLog("Command Running: {$process_info->created_at}");
        } else {
            // add store process
//            ProcessModel::addProcess($cm);

            try {
                $result = app()->call("App\Http\Controllers\CloneController@post", [
                    'setting' => [
                        'out'   => $this->out,
                        'limit' => $limit,
                    ]
                ]);

                $this->printLog($result);
            } catch (Exception $e) {
                // delete store process
                ProcessModel::deleteProcess($cm);

                $this->printLog($e->getMessage());
            }

            // delete store process
            ProcessModel::deleteProcess($cm);
        }
    }

    protected function printLog($message)
    {
        if ($this->out == 'live') {
            echo $message . "\n";
        }
    }
}
