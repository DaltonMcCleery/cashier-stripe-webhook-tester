<?php

namespace CashierStripeWebhookTester\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Laravel\Cashier\Http\Controllers\WebhookController;
use function Laravel\Prompts\{info, select};

class WebhookEventReplay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tester:stripe-replay {events=15}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replay a sequence of Stripe events, in order, to test webhook handling.';

    public function __construct(
        public $stripe = null,
    ) {
        parent::__construct();

        $this->stripe = new \Stripe\StripeClient(config('cashier.secret'));
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $events = $this->getEvents();

        $startingEvent = select(
            label: 'Select your Event starting point. We will replay all events from this point onwards.',
            options: collect($events)
                ->reverse()
                ->mapWithKeys(fn ($event) => [$event['id'] => $event['type'] . ' at ' . Carbon::createFromTimestamp($event['created'])->format('m/d/Y h:ia')])
                ->toArray(),
        );

        collect($events)
            ->reverse()
            ->skipUntil(fn ($event) => $event['id'] === $startingEvent)
            ->each(function ($event) {
                info('Processing event ' . $event['type'] . ' at ' . Carbon::createFromTimestamp($event['created'])->format('m/d/Y h:ia'));
                $this->triggerWebhook($event);
            });
    }

    protected function getEvents(): array
    {
        $response = $this->stripe->events->all(['limit' => $this->argument('events') ?? 15]);

        try {
            return $response->toArray()['data'];
        } catch (\Exception $exception) {
            // Nothing
        }

        return [];
    }

    protected function triggerWebhook($event): void
    {
        (new WebhookController())->handleWebhook(request: new Request(content: \json_encode($event)));
    }
}
