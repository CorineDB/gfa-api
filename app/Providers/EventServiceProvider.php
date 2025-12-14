<?php

namespace App\Providers;

use App\Events\Login;
use App\Jobs\GenererPta;
use App\Listeners\QueueBusyListener;
use App\Listeners\UserLogin;
use App\Models\LogActivity;
use App\Models\Notification;
use App\Models\UniteeDeGestion;
use App\Models\User;
use App\Observers\LogActivityObserver;
use App\Observers\UniteeDeGestionObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Facades\Event;

use function Illuminate\Events\queueable;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        // Login::class => [UserLogin::class],
        // Queue saturation alert - sends email when queue has too many jobs
        QueueBusy::class => [
            QueueBusyListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        LogActivity::observe(LogActivityObserver::class);
        User::observe(UserObserver::class);
        UniteeDeGestion::observe(UniteeDeGestionObserver::class);
        Event::listen(queueable(function (Login $event) {
            GenererPta::dispatch($event->programme)->delay(now());
        })->delay(now()->addSeconds(10)));
    }
}
