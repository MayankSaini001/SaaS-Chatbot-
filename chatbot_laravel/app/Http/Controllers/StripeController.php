<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Widget;

class StripeController extends Controller
{
    public static array $plans = [
        'basic' => [
            'name'       => 'Basic',
            'price'      => '$9/mo',
            'price_id'   => '',
            'agents'     => 2,
            'chats'      => 500,
            'features'   => ['2 agents', '500 chats/month', 'Basic widget', 'Email support'],
        ],
        'pro' => [
            'name'       => 'Pro',
            'price'      => '$29/mo',
            'price_id'   => '',
            'agents'     => 10,
            'chats'      => 5000,
            'features'   => ['10 agents', '5,000 chats/month', 'Custom widget', 'Priority support', 'File uploads'],
        ],
        'enterprise' => [
            'name'       => 'Enterprise',
            'price'      => '$99/mo',
            'price_id'   => '',
            'agents'     => 999,
            'chats'      => 999999,
            'features'   => ['Unlimited agents', 'Unlimited chats', 'White-label widget', 'Dedicated support', 'Custom integrations'],
        ],
    ];

    private function stripeClient(): \Stripe\StripeClient
    {
        $client = new \Stripe\StripeClient([
            'api_key'           => config('services.stripe.secret'),
            'http_client_telemetry' => false,
        ]);

        // SSL fix for shared hosting
        \Stripe\ApiRequestor::setHttpClient(
            new \Stripe\HttpClient\CurlClient([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ])
        );

        return $client;
    }

    public function pricing()
    {
        return view('billing.pricing', ['plans' => self::$plans]);
    }

    public function checkout(Request $request)
    {
        $plan = $request->plan;
        if (!isset(self::$plans[$plan])) abort(400);

        $priceId = config('services.stripe.prices.' . $plan);
        if (!$priceId) return back()->with('error', 'Plan not configured. Contact admin.');

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\ApiRequestor::setHttpClient(
            new \Stripe\HttpClient\CurlClient([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ])
        );

        $tenant = Auth::user()->tenant;

        $session = \Stripe\Checkout\Session::create([
            'mode'              => 'subscription',
            'customer_email'    => Auth::user()->email,
            'metadata'          => ['tenant_id' => $tenant->id, 'plan' => $plan],
            'line_items'        => [['price' => $priceId, 'quantity' => 1]],
            'success_url'       => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'        => route('billing.pricing'),
            'subscription_data' => ['metadata' => ['tenant_id' => $tenant->id, 'plan' => $plan]],
        ]);

        return redirect($session->url);
    }

    public function success(Request $request)
    {
        return view('billing.success');
    }

    public function billing()
    {
        $tenant  = Auth::user()->tenant;
        $invoices = [];

        if ($tenant->stripe_id) {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            \Stripe\ApiRequestor::setHttpClient(
                new \Stripe\HttpClient\CurlClient([
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                ])
            );
            try {
                $list = \Stripe\Invoice::all(['customer' => $tenant->stripe_id, 'limit' => 10]);
                $invoices = $list->data;
            } catch (\Exception $e) {
                Log::error('Stripe invoice fetch error: ' . $e->getMessage());
            }
        }

        return view('billing.dashboard', [
            'tenant'   => $tenant,
            'plans'    => self::$plans,
            'invoices' => $invoices,
        ]);
    }

    public function cancel(Request $request)
    {
        $tenant = Auth::user()->tenant;
        if (!$tenant->stripe_id) return back()->with('error', 'No active subscription.');

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        \Stripe\ApiRequestor::setHttpClient(
            new \Stripe\HttpClient\CurlClient([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ])
        );

        try {
            $subscriptions = \Stripe\Subscription::all(['customer' => $tenant->stripe_id, 'status' => 'active', 'limit' => 1]);
            if ($subscriptions->data) {
                \Stripe\Subscription::update($subscriptions->data[0]->id, ['cancel_at_period_end' => true]);
            }
            return back()->with('success', 'Subscription cancelled. Active till end of billing period.');
        } catch (\Exception $e) {
            Log::error('Stripe cancel error: ' . $e->getMessage());
            return back()->with('error', 'Could not cancel. Contact support.');
        }
    }

    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session  = $event->data->object;
                $tenantId = $session->metadata->tenant_id ?? null;
                $plan     = $session->metadata->plan ?? 'basic';
                if (!$tenantId) break;
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    $tenant->update([
                        'is_active'       => true,
                        'plan'            => $plan,
                        'stripe_id'       => $session->customer,
                        'stripe_status'   => 'active',
                        'stripe_price_id' => $session->subscription ?? null,
                    ]);
					
					\App\Models\Widget::where('tenant_id', $tenant->id)
					->update([
					'is_active' => true
					]);
					
                    try { \Mail::to($tenant->email)->send(new \App\Mail\SubscriptionActivatedMail($tenant, $plan)); } catch (\Exception $e) {}
                }
                break;

            case 'customer.subscription.updated':
                $sub      = $event->data->object;
                $tenantId = $sub->metadata->tenant_id ?? null;
                if (!$tenantId) break;
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    $plan = $this->getPlanFromPriceId($sub->items->data[0]->price->id ?? '');
                    $tenant->update([
                        'stripe_status'        => $sub->status,
                        'stripe_price_id'      => $sub->items->data[0]->price->id ?? $tenant->stripe_price_id,
                        'plan'                 => $plan ?: $tenant->plan,
                        'is_active'            => $sub->status === 'active',
                        'subscription_ends_at' => $sub->cancel_at ? \Carbon\Carbon::createFromTimestamp($sub->cancel_at) : null,
                    ]);
                }
                break;

            case 'customer.subscription.deleted':
                $sub    = $event->data->object;
                $tenant = Tenant::where('stripe_id', $sub->customer)->first();
                if ($tenant) {
                    $tenant->update(['is_active' => false, 'stripe_status' => 'cancelled']);
                    try { \Mail::to($tenant->email)->send(new \App\Mail\SubscriptionCancelledMail($tenant)); } catch (\Exception $e) {}
                }
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $tenant  = Tenant::where('stripe_id', $invoice->customer)->first();
                if ($tenant) {
                    $tenant->update(['stripe_status' => 'past_due']);
                    try { \Mail::to($tenant->email)->send(new \App\Mail\PaymentFailedMail($tenant)); } catch (\Exception $e) {}
                }
                break;

            case 'invoice.payment_succeeded':
                $invoice = $event->data->object;
                $tenant  = Tenant::where('stripe_id', $invoice->customer)->first();
                if ($tenant && $tenant->stripe_status !== 'active') {
                    $tenant->update(['is_active' => true, 'stripe_status' => 'active']);
                }
                break;
        }

        return response()->json(['status' => 'ok']);
    }

    private function getPlanFromPriceId(string $priceId): ?string
    {
        foreach (['basic','pro','enterprise'] as $plan) {
            if (config('services.stripe.prices.' . $plan) === $priceId) return $plan;
        }
        return null;
    }
}