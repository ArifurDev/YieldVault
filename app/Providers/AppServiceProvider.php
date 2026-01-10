<?php

namespace App\Providers;

use App\Models\User;
use App\Notifications\UniversalNotification;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Apple\Provider as AppleProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\DataEncryptionService::class, function ($app) {
            return new \App\Services\DataEncryptionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Socialite::extend('apple', static function ($app) {
            $config = $app['config']['services.apple'];

            return new AppleProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });



        Queue::failing(function (JobFailed $event) {

            //  Only notify for ROI job (optional but recommended)
            if (! str_contains($event->job->resolveName(), 'ProcessDailyInvestmentROI')) {
                return;
            }

            // Get admin (adjust role logic if needed)
            $admin = User::where('role', 'admin')->first();

            if (! $admin) {
                return;
            }

            $admin->notify(
                new UniversalNotification(
                    'Queue Job Failed',
                    'Daily ROI job failed. Error: ' . $event->exception->getMessage(),
                    'danger',
                    [
                        'job'      => $event->job->resolveName(),
                        'queue'    => $event->job->getQueue(),
                        'failed_at'=> now(),
                        'action'   => 'queue_failed',
                    ]
                )
            );
        });


    }
}
