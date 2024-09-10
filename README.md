# Cashier Stripe Webhook Tester

Testing Stripe events and webhook handling locally without Ngrok.

## Requirements

You will need to have the [Laravel Cashier - Stripe](https://laravel.com/docs/11.x/billing) package installed and your
Stripe API keys set up in your `.env` file.

## Install
```
composer require --dev daltonmccleery/cashier-stripe-webhook-tester
```

## Usage

Continuously poll for the new events dispatched by Stripe when making transactions or updating customers and subscriptions.

```bash
php artisan tester:stripe-listen
```

Replay a sequence of Stripe events, in order, and fire their appropriate webhook.

```bash
php artisan tester:stripe-replay
```
