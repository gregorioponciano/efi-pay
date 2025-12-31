<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PixController;

// Página inicial
Route::get('/', [PixController::class, 'index']);

// Teste de conexão
Route::get('/testar', [PixController::class, 'testar']);

// API PIX (rotas principais)
Route::post('/gerar-pix', [PixController::class, 'gerarPix']);
Route::get('/consultar-pix/{txid}', [PixController::class, 'consultarPix']);

// Rota para teste rápido via navegador
Route::get('/gerar-teste', function() {
    // Gera um PIX de teste R$ 1,00
    return redirect()->route('gerar.pix.teste');
});


// routes/web.php
Route::get('/pix-teste', function() {
    // Teste direto sem view
    $client = new \App\Http\Controllers\PixController();
    
    // Cria request fake
    $request = new \Illuminate\Http\Request([
        'valor' => 1.00,
        'descricao' => 'Teste PIX'
    ]);
    
    return $client->gerarPix($request);
});