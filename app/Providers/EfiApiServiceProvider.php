<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

class EfiApiServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('efi.http', function () {
            return Http::withOptions([
                'base_uri' => 'https://pix-h.api.efipay.com.br/v2/',
                'cert' => storage_path('cert/certificado.pem'),
                'ssl_key' => storage_path('cert/chave.pem'),
                'ssl_key_password' => env('EFI_CERT_PASSWORD'),
                'timeout' => 30,
                'connect_timeout' => 10,
                'verify' => true,
            ])->withHeaders([
                'Content-Type' => 'application/json',
            ]);
        });
    }
    
}