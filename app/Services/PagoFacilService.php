<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PagoFacilService
{
    protected string $baseUrl;
    protected string $tokenService;
    protected string $tokenSecret;

    public function __construct()
    {
        $this->baseUrl      = config('pagofacil.base_url');
        $this->tokenService = config('pagofacil.token_service');
        $this->tokenSecret  = config('pagofacil.token_secret');
    }

    /**
     * 1) Autenticación: obtener accessToken (con caché de 50 minutos)
     */
    public function login(): string
    {
        // Intentar obtener token desde caché
        $cachedToken = Cache::get('pagofacil_access_token');
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::withHeaders([
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret'  => $this->tokenSecret,
                'Accept'         => 'application/json',
            ])->post("{$this->baseUrl}/login");

            $data = $response->json();

            Log::info('PagoFacil login response', $data);

            if (($data['error'] ?? 1) !== 0) {
                throw new \RuntimeException(
                    'Error en login PagoFacil: ' . ($data['message'] ?? 'Respuesta inválida')
                );
            }

            $accessToken = $data['values']['accessToken'];
            $expiresIn = $data['values']['expiresInMinutes'] ?? 55;

            // Cachear token por 50 minutos (5 minutos antes de que expire)
            Cache::put('pagofacil_access_token', $accessToken, now()->addMinutes($expiresIn - 5));

            return $accessToken;
        } catch (\Exception $e) {
            Log::error('Error en PagoFacil login', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 2) Listar métodos habilitados y devolver el ID del método QR
     */
    public function getQrPaymentMethodId(string $accessToken): int
    {
        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post("{$this->baseUrl}/list-enabled-services");

            $data = $response->json();

            Log::info('PagoFacil list-enabled-services response', $data);

            if (($data['error'] ?? 1) !== 0) {
                throw new \RuntimeException(
                    'Error listando métodos habilitados: ' . ($data['message'] ?? 'Respuesta inválida')
                );
            }

            foreach ($data['values'] ?? [] as $method) {
                // Buscar método que contenga "QR" en el nombre
                if (isset($method['paymentMethodName']) &&
                    str_contains(strtoupper($method['paymentMethodName']), 'QR')) {
                    return (int) $method['paymentMethodId'];
                }
            }

            throw new \RuntimeException('No se encontró método QR habilitado.');
        } catch (\Exception $e) {
            Log::error('Error obteniendo método QR', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * 3) Generar QR para una orden
     */
    public function generateQrForOrder(
        string $accessToken,
        int $paymentMethodId,
        array $payload
    ): array {
        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post("{$this->baseUrl}/generate-qr", $payload);

            $data = $response->json();

            Log::info('PagoFacil generate-qr response', $data);

            if (($data['error'] ?? 1) !== 0) {
                throw new \RuntimeException(
                    'Error generando QR: ' . ($data['message'] ?? 'Respuesta inválida')
                );
            }

            return $data['values'] ?? [];
        } catch (\Exception $e) {
            Log::error('Error generando QR', ['error' => $e->getMessage(), 'payload' => $payload]);
            throw $e;
        }
    }

    /**
     * 4) Consultar estado de una transacción
     */
    public function queryTransaction(string $accessToken, array $criteria): array
    {
        try {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post("{$this->baseUrl}/query-transaction", $criteria);

            $data = $response->json();

            Log::info('PagoFacil query-transaction response', $data);

            if (($data['error'] ?? 1) !== 0) {
                throw new \RuntimeException(
                    'Error consultando transacción: ' . ($data['message'] ?? 'Respuesta inválida')
                );
            }

            return $data['values'] ?? [];
        } catch (\Exception $e) {
            Log::error('Error consultando transacción', ['error' => $e->getMessage(), 'criteria' => $criteria]);
            throw $e;
        }
    }
}
