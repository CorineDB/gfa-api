<?php

namespace App\Console\Commands;

use App\Jobs\PasswordExpirationJob;
use App\Models\User;
use App\Traits\Helpers\ConfigueTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendPasswordValidityExpirationSoonMail extends Command
{
    use ConfigueTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send-password-validity-expiration-soon-mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users of expiration of their password';

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
        $this->sendPasswordValidityExpirationSoonMail();
        $this->info($this->description);
        return 1;
    }

    public function sendPasswordValidityExpirationSoonMail(){

        $users = User::select(DB::raw('DATE_ADD(DATE_FORMAT(password_update_at, "%Y-%m-%d"), INTERVAL '. $this->periodeValiditerMotDePasse. ' DAY) as futur'), DB::raw('DATE_FORMAT(curdate(), "%Y-%m-%d") as today'), 'email', 'password_update_at', 'nom', 'prenom')->whereNotNull('password_update_at')->get();

        $users = $users->where('futur', '=', Carbon::now()->format("Y-m-d"));

        foreach ($users as $user) {
            dispatch(new PasswordExpirationJob($user)); 
        }

        $this->info($users);
    }
}
