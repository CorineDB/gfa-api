<?php

namespace App\Jobs;

use App\Mail\PasswordExpirationNotification;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class PasswordExpirationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $emails;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
/* 
        try {

            $details = [];

            $details['view'] = "emails.auth.password_expiration";
            $details['subject'] = "Expiration de mot de passe";
            $details['content'] = [
                "greeting" => "Bienvenu Mr/Mme " . $this->user->nom,
                "introduction" => "La date d'expiration de votre mot de passe est en approche. Veuillez penser à le changer.",
            ];

            $when = now()->addSeconds(0);

            Mail::to($this->user)->later($when, new PasswordExpirationNotification($details));

        } catch (\Throwable $th) {
            throw new Exception("Error Processing Request : ". $details, 1);
        }
 */

    }
}
