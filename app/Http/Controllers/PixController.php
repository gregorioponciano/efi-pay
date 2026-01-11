<?php
// app/Http/Controllers/PixController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PixController extends Controller
{
    // Método 1: GERAR PIX
    public function gerarPix(Request $request)
    {
        // Validação simples
        $request->validate([
            'valor' => 'required|numeric|min:0.01',
            'descricao' => 'nullable|string|max:255',
        ]);
        
        // 1. Primeiro pega o token de acesso
        $token = $this->getAccessToken();
        
        if (!$token) {
            return response()->json(['error' => 'Falha ao obter token de acesso'], 500);
        }
        
        // 2. Cria a cobrança PIX
        $dadosPix = [
            'calendario' => [
                'expiracao' => 3600 // 1 hora
            ],
            'valor' => [
                'original' => number_format($request->valor, 2, '.', '')
            ],
            'chave' => config('efi.chave_pix'),
            'solicitacaoPagador' => $request->descricao ?: 'Pagamento via PIX'
        ];
        
        try {
            $response = Http::withOptions([
                'cert' => config('efi.cert_path'),
                'ssl_key' => config('efi.cert_path'), // Mesmo arquivo
                'verify' => false, // Para teste
            ])->withToken($token)
              ->post('https://pix-h.api.efipay.com.br/v2/cob', $dadosPix);
            
            if ($response->successful()) {
                $dados = $response->json();
                
                // Retorna dados do PIX
                return response()->json([
                    'success' => true,
                    'pix' => [
                        'txid' => $dados['txid'],
                        'valor' => $dados['valor']['original'],
                        'qr_code' => $this->gerarQrCode($dados['loc']['id']),
                        'copia_cola' => $dados['pixCopiaECola'],
                        'expira_em' => $dados['calendario']['expiracao'] . ' segundos'
                    ]
                ]);
            }
            
            return response()->json([
                'error' => 'Erro ao gerar PIX',
                'detalhes' => $response->json()
            ], 400);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // Método 2: CONSULTAR PIX
    public function consultarPix($txid)
    {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return response()->json(['error' => 'Falha ao obter token'], 500);
        }
        
        try {
            $response = Http::withOptions([
                'cert' => config('efi.cert_path'),
                'ssl_key' => config('efi.cert_path'),
                'verify' => true,
            ])->withToken($token)
              ->get('https://pix-h.api.efipay.com.br/v2/cob/' . $txid);
            
            return response()->json($response->json());
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    // Método PRIVADO: Pegar token de acesso
    private function getAccessToken()
    {
        try {
            $response = Http::withOptions([
                'cert' => config('efi.cert_path'),
                'ssl_key' => config('efi.key_path'),
                'verify' => false,
            ])->withBasicAuth(
                config('efi.client_id'),
                config('efi.client_secret')
            )->asForm()->post(config('efi.oauth_url'), [
                'grant_type' => 'client_credentials'
            ]);
            
            if ($response->successful()) {
                return $response->json()['access_token'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    // Método PRIVADO: Gerar QR Code
    private function gerarQrCode($locationId)
    {
        $token = $this->getAccessToken();
        
        if (!$token) return null;
        
        try {
            $response = Http::withOptions([
                'cert' => config('efi.cert_path'),
                'ssl_key' => config('efi.cert_path'),
                'verify' => true,
            ])->withToken($token)
              ->get('https://pix-h.api.efipay.com.br/v2/loc/' . $locationId . '/qrcode');
            
            if ($response->successful()) {
                return $response->json()['imagemQrcode'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    // Método 3: Página inicial SIMPLES
    public function index()
    {
        return view('pix.index'); // Vamos criar essa view
    }
    
    // Método 4: TESTE de conexão
    public function testar()
    {
        // Verifica se certificado existe
        if (!file_exists(config('efi.cert_path'))) {
            return "❌ Certificado não encontrado em: " . config('efi.cert_path');
        }
        
        // Verifica se tem as credenciais
        if (!config('efi.client_id') || !config('efi.client_secret')) {
            return "❌ Configure EFI_CLIENT_ID e EFI_CLIENT_SECRET no .env";
        }
        
        // Tenta pegar token
        $token = $this->getAccessToken();
        
        if ($token) {
            return "✅ Conexão OK! Token obtido com sucesso.";
        } else {
            return "❌ Falha na conexão. Verifique: <br>
                    1. Certificado em: " . config('efi.cert_path') . "<br>
                    2. Credenciais no .env<br>
                    3. Se o certificado contém chave privada";
        }
    }
}