<?php

namespace CashierStripeWebhookTester;

use CashierStripeWebhookTester\Console\Commands\WebhookEventListener;
use CashierStripeWebhookTester\Console\Commands\WebhookEventReplay;
use Illuminate\Support\ServiceProvider;

/**
 * Class CashierStripeWebhookTesterServiceProvider.
 */
class CashierStripeWebhookTesterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookEventListener::class,
                WebhookEventReplay::class,
            ]);
        }
    }
}
