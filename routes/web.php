<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PixController;

/*
|--------------------------------------------------------------------------
| Página inicial
|--------------------------------------------------------------------------
| Apenas exibe a tela com formulário PIX
*/
Route::get('/', [PixController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Gerar PIX (POST)
|--------------------------------------------------------------------------
| Recebe dados do formulário
*/
Route::post('/gerar-pix', [PixController::class, 'gerarPix'])
    ->name('pix.gerar');

/*
|--------------------------------------------------------------------------
| Consultar PIX
|--------------------------------------------------------------------------
*/
Route::get('/consultar-pix/{txid}', [PixController::class, 'consultarPix'])
    ->name('pix.consultar');

/*
|--------------------------------------------------------------------------
| PIX de teste via navegador
|--------------------------------------------------------------------------
| Chama o controller corretamente
*/
Route::get('/pix-teste', function () {
    return app(PixController::class)->gerarPix(
        request()->merge([
            'valor' => 1.00,
            'descricao' => 'Teste PIX'
        ])
    );
})->name('pix.teste');
