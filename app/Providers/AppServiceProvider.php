<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        Paginator::defaultView('vendor.pagination.vuexy');

        // Vuexy: tema dosyalarının <link> etiketlerini customizer'ın tanıyacağı sınıflarla işaretle
        Vite::useStyleTagAttributes(function (?string $src, string $url, ?array $chunk, ?array $manifest) {
            if ($src === null) {
                return [];
            }

            return [
                'class' => match (true) {
                    (bool) preg_match('/(resources\/assets\/vendor\/scss\/(rtl\/)?core)-?.*/i', $src) => 'template-customizer-core-css',
                    (bool) preg_match('/(resources\/assets\/vendor\/scss\/(rtl\/)?theme)-?.*/i', $src) => 'template-customizer-theme-css',
                    default => '',
                },
            ];
        });
    }
}
