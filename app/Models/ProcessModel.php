<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ProcessModel
{
    public static function addProcess($command)
    {
        return DB::connection('sqlite')->table('process')->insertGetId([
            'command'    => $command,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function deleteProcess($command)
    {
        DB::connection('sqlite')->table('process')->where('command', $command)->delete();
    }

    public static function getProcess($command)
    {
        return DB::connection('sqlite')->table('process')->where('command', $command)->distinct()->first();
    }

    public static function runProcess($command)
    {
        $process_info = self::getProcess($command);

        if (!$process_info) {
            $process_info = null;
        } else {
            $limit = 6; // hour

            $created_at = $process_info->created_at;

            $date_new = date('Y-m-d H:i:s', strtotime("{$created_at} +{$limit} hour"));
            $date_now = date('Y-m-d H:i:s');

            if ($date_now > $date_new) {
                self::deleteProcess($command);

                $process_info = null;
            }
        }

        return $process_info;
    }
}