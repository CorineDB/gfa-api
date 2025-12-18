<?php

namespace App\Observers;

use App\Models\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LogActivityObserver
{

    /**
     * Handle the LogActivity "creating" event.
     *
     * @param  \App\Models\LogActivity  $user
     * @return void
     */
    public function creating(LogActivity $logActivity)
    {
    	$logActivity['ipAdresse'] = request()->ip();
    	$logActivity['userAgent'] = request()->header('user-agent');
    }

    /**
     * Handle the LogActivity "created" event.
     *
     * @param  \App\Models\LogActivity  $logActivity
     * @return void
     */
    public function created(LogActivity $logActivity)
    {
        //
    }

    /**
     * Handle the LogActivity "updated" event.
     *
     * @param  \App\Models\LogActivity  $logActivity
     * @return void
     */
    public function updated(LogActivity $logActivity)
    {
        //
    }

    /**
     * Handle the LogActivity "deleted" event.
     *
     * @param  \App\Models\LogActivity  $logActivity
     * @return void
     */
    public function deleted(LogActivity $logActivity)
    {
        //
    }

    /**
     * Handle the LogActivity "restored" event.
     *
     * @param  \App\Models\LogActivity  $logActivity
     * @return void
     */
    public function restored(LogActivity $logActivity)
    {
        //
    }

    /**
     * Handle the LogActivity "force deleted" event.
     *
     * @param  \App\Models\LogActivity  $logActivity
     * @return void
     */
    public function forceDeleted(LogActivity $logActivity)
    {
        //
    }
}
