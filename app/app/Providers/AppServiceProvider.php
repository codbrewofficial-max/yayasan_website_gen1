<?php

namespace App\Providers;

use App\Services\Payment\MidtransGateway;
use App\Services\Payment\PaymentGateway;
use App\Services\Payment\StubPaymentGateway;
use App\Support\TenantContext;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, fn () => new TenantContext);

        $this->app->singleton(PaymentGateway::class, function () {
            $config = config('payment.midtrans');

            if (empty($config['server_key'])) {
                return new StubPaymentGateway;
            }

            return new MidtransGateway($config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
