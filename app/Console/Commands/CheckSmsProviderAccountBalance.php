<?php

namespace App\Console\Commands;

use App\Jobs\SmsProviderAlertAccountBalanceJob;
use App\Models\Notification;
use App\Notifications\SmsProviderAlertAccountBalanceNotification;
use App\Traits\Helpers\SmsTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckSmsProviderAccountBalance extends Command
{
    use SmsTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-sms-provider:account-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the SMS provider account balance and send an alert if low.';
    

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

        $balance = $this->getSmsBalance();  // Assume this function fetches the current SMS balance

        if ($balance <= 500) {
            // Send both Email and SMS for Critical Alert
            $this->sendCriticalAlert($balance, "Critical Alert: Your SMS balance is critically low : $balance. Please top-up ASAP!");
        } elseif ($balance <= 1000) {
            $this->sendLowBalanceAlert("Warning: Your SMS balance is getting low. Please consider topping up soon.", 'warning');
        } elseif ($balance <= 2500) {
            $this->sendLowBalanceAlert("Warning: Your SMS balance is getting low. Please consider topping up soon.", 'warning');
        }

        return 0;
    }

    /**
     * Send an email alert if the SMS balance is low.
     */
    private function sendLowBalanceAlert($balance, $subject = null, $message = null)
    {
        $recipients = explode(',', str_replace(['[', ']'], '', env('ALERT_EMAIL', 'admin@example.com')));

        $message = $message ?? "Warning! Your SMS balance is low.\nCurrent balance: {$balance} messages.\nPlease top up soon.";
        $subject = $subject ?? '⚠️ Low SMS Balance Alert';

        SmsProviderAlertAccountBalanceJob::dispatch($message,'',$recipients)->delay(30);

        Log::notice("$subject! Alert email sent to ".json_encode($recipients));
        $this->warn("$subject! Alert email sent to ".json_encode($recipients));
    }

    public function sendCriticalAlert($balance, $message)
    {
        // Send Email Alert
        $this->sendLowBalanceAlert($balance, "Critical Alert: SMS Balance Status", $message);
    
        // Send SMS Alert (Assuming $this->sendSms is the function to send an SMS)
        $phoneNumbers = explode(',', env('ALERT_SMS', '2290196970603,2290162004867')); // Add phone numbers here

        SmsProviderAlertAccountBalanceJob::dispatch($message,'','',$phoneNumbers)->delay(30);

        Log::notice("Alert sms sent to ".json_encode($phoneNumbers));
    }
}
