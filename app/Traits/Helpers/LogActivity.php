<?php


namespace App\Traits\Helpers;

use App\Models\LogActivity as LogActivityModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogActivity
{

    public static function addToLog($logName, $description, $type, $modelId)
    {
    	$log = [];
    	$log['log_name'] = $logName;
    	$log['description'] = $description;
    	$log['properties'] = "{}";
    	$log['subject_type'] = $type;
    	$log['subject_id'] = $modelId;
    	$log['causer_type'] = "App\\Models\\User";
    	$log['causer_id'] = request()->user()->id ?? 0;
    	$log['ipAdresse'] = request()->ip();
    	$log['userAgent'] = request()->header('user-agent');
    	$logActivity['ipAdresse'] = request()->ip();
    	$logActivity['userAgent'] = request()->header('user-agent');

    	LogActivityModel::create($log);
    }


    public static function logActivityLists()
    {
    	return LogActivityModel::latest()->get();
    }

}