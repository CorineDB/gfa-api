<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;
use Illuminate\Support\Facades\Log;

class AppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $action;

    // Define the maximum number of attempts to retry the job
    public int $tries = 3;

    // Define the timeout for the job (in seconds)
    public int $timeout = 30;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($action)
    {
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Execute the action if it is callable
            if (is_callable($this->action)) {
                call_user_func($this->action);
            }
        } catch (Exception $e) {
            // Log the exception and rethrow it to allow retry
            Log::error("AppJob failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Send notification, alert, or log the failure
        Log::error("AppJob permanently failed after retries: " . $exception->getMessage());
    }

    /**
     * Delay the job processing.
     *
     * @return \DateTime
     */
    public function delay()
    {
        // Return a delay interval for the job, for example, 10 seconds
        return now()->addMinutes(10);
    }
}
