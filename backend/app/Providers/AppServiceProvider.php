<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Invoices\InvoiceXmlParser;
use App\Domain\Invoices\NationalNfseXmlParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            InvoiceXmlParser::class,
            fn (): NationalNfseXmlParser => new NationalNfseXmlParser(
                resource_path('schemas/nfse')
            )
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
