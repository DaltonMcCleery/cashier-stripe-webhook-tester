<?php

namespace CashierStripeWebhookTester\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController;
use function Laravel\Prompts\{info, select};

class WebhookEventListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tester:stripe-listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously poll for the most recent events dispatched by Stripe.';

    public function __construct(
        public $stripe = null,
    ) {
        parent::__construct();

        $this->stripe = new \Stripe\StripeClient(config('cashier.secret'));
    }

    public function handle(): void
    {
        // Continuously run command to fetch updated Stripe models and simulate events
        info('Starting Stripe event listener... [ctrl + c to stop]');

        // Fetch the most recent events every 10 seconds.
        while (true) {
            // Use timestamp to see if the event occurred in the last 10 seconds.
            // Add 1 second for any API call latency.
            $timestamp = now()->subSeconds(11);
            $this->callApi($timestamp);

            usleep(100 * 100000);
        }
    }

    protected function callApi($timestamp): void
    {
        // Call Stripe API
        $response = $this->stripe->events->all(['created' => ['gte' => $timestamp->timestamp]]);

        collect($response->toArray()['data'])
            ->each(function ($event) {
                info('Processing event ' . $event['type'] . ' at ' . Carbon::createFromTimestamp($event['created'])->format('m/d/Y h:ia'));
                $this->triggerWebhook($event);
            });
    }

    protected function triggerWebhook($event): void
    {
        (new WebhookController())->handleWebhook(request: new Request(content: \json_encode($event)));
    }
}
