<?php

namespace App\Providers;

use App\Services\MailerService;
use App\Services\MailjetProvider;
use App\Services\SendgridProvider;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('HttpClient', function ($app) {
            return new Client();
        });
        
        $this->app->singleton('MailjetProvider', function ($app) {
            $config = $app->make('config')->get('mailerproviders.providers.mailjet', []);
                        
            $username = Arr::pull($config, 'username', null);
            $password = Arr::pull($config, 'password', null);
            return new MailjetProvider($app->make('HttpClient'), $username, $password);
        });

        $this->app->singleton('SendgridProvider', function ($app) {
            $config = $app->make('config')->get('mailerproviders.providers.sendgrid', []);
            
            $token = Arr::pull($config, 'token', null);
            return new SendgridProvider($app->make('HttpClient'), $token);
        });
        
        $this->app->tag(['MailjetProvider', 'SendgridProvider'], 'providers');
        
        $this->app->singleton(MailerService::class, function ($app) {
            $config = $app->make('config')->get('mailerproviders', []);
            
            $from = Arr::pull($config, 'sender', null);
            $replyTo = Arr::pull($config, 'reply_to', null);
            $defaultProviderName = Arr::pull($config, 'default', null);
            
            return new MailerService($app->tagged('providers'), $from, $replyTo, $defaultProviderName);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        
        Validator::extend('recipients', 'App\Rules\Recipients@validate');
        Validator::replacer('recipients', 'App\Rules\Recipients@message');
    }
}
