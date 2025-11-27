
---

````md
# 🧾 Integración de PagoFacil QR en Laravel (Lavandería BELÉN)

Esta guía describe **paso a paso** cómo integrar la API **MasterQR** de PagoFacil en un proyecto Laravel (Laravel + Inertia + Vue), usando la versión 1.1.0 de la documentación oficial de integración por API, método QR. :contentReference[oaicite:0]{index=0}  

La guía está enfocada en:

- Evitar errores frecuentes (URL callback incorrecta, mal uso de tokens, etc.).
- Integrar el pago con QR al flujo de **órdenes** de la Lavandería BELÉN.
- Cumplir el requisito de **pagos electrónicos (QR)** en Tecnología Web.

---

## 1. Resumen de la API MasterQR

### 1.1. URL base y protocolo

Según la documentación oficial: :contentReference[oaicite:1]{index=1}  

- **Protocolo:** HTTPS  
- **URL_BASE:**  
  `https://masterqr.pagofacil.com.bo/api/services/v2`

Todos los endpoints de la API se construyen sobre esta base.

### 1.2. Flujo general de pago

El flujo recomendado por PagoFacil es: :contentReference[oaicite:2]{index=2}  

1. **Autenticación (`/login`)** → obtienes `accessToken`.
2. **Generar QR (`/generate-qr`)** → con el token generas el QR de la transacción.
3. **Mostrar QR al cliente** → renderizar imagen QR Base64.
4. **Cliente escanea y paga** con su app bancaria.
5. **Confirmación del pago**, de dos formas:
   - **Callback (recomendado)**: PagoFacil hace POST a tu `callbackUrl`.
   - **Consulta manual**: POST a `/query-transaction`.
6. **Finalizar venta**: actualizas la orden y registras el pago en tu BD.

Todas las respuestas siguen esta estructura estándar: :contentReference[oaicite:3]{index=3}  

```json
{
  "error": 0,
  "status": 1,
  "message": "Texto...",
  "values": { ... }
}
````

> Regla general:
>
> * `error == 0` → OK
> * `error != 0` → hubo problema (lee `message`).

---

## 2. Configuración en Laravel

### 2.1. Variables de entorno `.env`

Agrega al `.env` de tu proyecto:

```env
# PagoFacil MasterQR
PAGOFACIL_BASE_URL=https://masterqr.pagofacil.com.bo/api/services/v2

PAGOFACIL_TOKEN_SERVICE=tu_tcTokenService
PAGOFACIL_TOKEN_SECRET=tu_tcTokenSecret

# Identificador interno (código de cliente) opcional
PAGOFACIL_CLIENT_CODE=LAVANDERIA_BELEN

# URL callback y retorno (deben ser URL públicas)
PAGOFACIL_CALLBACK_URL=https://www.tecnoweb.org.bo/inf513/grupo26sc/lavanderia/payment/callback
PAGOFACIL_RETURN_URL=https://www.tecnoweb.org.bo/inf513/grupo26sc/lavanderia/payment/return
```

> ⚠ La `PAGOFACIL_CALLBACK_URL` **debe coincidir exactamente** con la URL que Pongas en `callbackUrl` cuando generes el QR. Si no, PagoFacil pondrá tu pago en **estado 5 – Revisión** (caso que les pasó a varios compañeros cuando su URL era inválida).

### 2.2. Config opcional `config/pagofacil.php`

Crea `config/pagofacil.php`:

```php
<?php

return [
    'base_url'       => env('PAGOFACIL_BASE_URL', 'https://masterqr.pagofacil.com.bo/api/services/v2'),
    'token_service'  => env('PAGOFACIL_TOKEN_SERVICE'),
    'token_secret'   => env('PAGOFACIL_TOKEN_SECRET'),
    'client_code'    => env('PAGOFACIL_CLIENT_CODE', 'LAVANDERIA_BELEN'),
    'callback_url'   => env('PAGOFACIL_CALLBACK_URL'),
    'return_url'     => env('PAGOFACIL_RETURN_URL'),
];
```

---

## 3. Endpoints oficiales

Basado en el JSON de Postman y el PDF:

1. `POST /login`

   * Headers:

     * `tcTokenService`
     * `tcTokenSecret`
   * Body: vacío.

2. `POST /list-enabled-services`

   * Header:

     * `Authorization: Bearer <accessToken>`

3. `POST /generate-qr`

   * Header:

     * `Authorization: Bearer <accessToken>`
   * Body JSON:

     ```json
     {
       "paymentMethod": 4,
       "clientName": "Jhon Doe",
       "documentType": 1,
       "documentId": "123456",
       "phoneNumber": "75540850",
       "email": "correo@ejemplo.com",
       "paymentNumber": "ID_Transaccion_Empresa",
       "amount": 0.1,
       "currency": 2,
       "clientCode": "11001",
       "callbackUrl": "https://tu-dominio.com/callback",
       "orderDetail": [
         {
           "serial": 1,
           "product": "Detalle_Item",
           "quantity": 1,
           "price": 0.1,
           "discount": 0,
           "total": 0.1
         }
       ]
     }
     ```



4. `POST /query-transaction`

   * Header:

     * `Authorization: Bearer <accessToken>`
   * Body:

     ```json
     {
       "pagofacilTransactionId": "Id_Transaccion_PagoFacil",
       "companyTransactionId": "Id_Transaccion_Empresa"
     }
     ```

     (solo uno de los dos es requerido). 

5. `GET /bibliography/{parameterName}` (opcional, para ayuda de parámetros). 

---

## 4. Clase de servicio en Laravel

Usaremos el **HTTP Client** de Laravel (`Illuminate\Support\Facades\Http`).

Crea `app/Services/PagoFacilService.php`:

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
     * 1) Autenticación: obtener accessToken
     */
    public function login(): string
    {
        $response = Http::withHeaders([
            'tcTokenService' => $this->tokenService,
            'tcTokenSecret'  => $this->tokenSecret,
            'Accept'         => 'application/json',
        ])->post("{$this->baseUrl}/login");

        $data = $response->json();

        if (($data['error'] ?? 1) !== 0) {
            throw new \RuntimeException(
                'Error en login PagoFacil: ' . ($data['message'] ?? 'Respuesta inválida')
            );
        }

        return $data['values']['accessToken'];
    }

    /**
     * 2) Listar métodos habilitados y devolver el ID del método QR
     */
    public function getQrPaymentMethodId(string $accessToken): int
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("{$this->baseUrl}/list-enabled-services");

        $data = $response->json();

        if (($data['error'] ?? 1) !== 0) {
            throw new \RuntimeException(
                'Error listando métodos habilitados: ' . ($data['message'] ?? 'Respuesta inválida')
            );
        }

        foreach ($data['values'] ?? [] as $method) {
            // Suele venir algo como "QR ..."
            if (isset($method['paymentMethodName']) &&
                str_contains(strtoupper($method['paymentMethodName']), 'QR')) {
                return (int) $method['paymentMethodId'];
            }
        }

        throw new \RuntimeException('No se encontró método QR habilitado.');
    }

    /**
     * 3) Generar QR para una orden
     */
    public function generateQrForOrder(
        string $accessToken,
        int $paymentMethodId,
        array $payload
    ): array {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("{$this->baseUrl}/generate-qr", $payload);

        $data = $response->json();

        if (($data['error'] ?? 1) !== 0) {
            throw new \RuntimeException(
                'Error generando QR: ' . ($data['message'] ?? 'Respuesta inválida')
            );
        }

        return $data['values'] ?? [];
    }

    /**
     * 4) Consultar estado de una transacción
     */
    public function queryTransaction(string $accessToken, array $criteria): array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("{$this->baseUrl}/query-transaction", $criteria);

        $data = $response->json();

        if (($data['error'] ?? 1) !== 0) {
            throw new \RuntimeException(
                'Error consultando transacción: ' . ($data['message'] ?? 'Respuesta inválida')
            );
        }

        return $data['values'] ?? [];
    }
}
```

> Recomendación: puedes cachear el `accessToken` en sesión o caché durante unos minutos (`expiresInMinutes` del login) para no llamar `/login` en cada petición. 

---

## 5. Integración con la Orden de Lavandería

Supongamos que tienes el modelo `Orden` con PK `nro`, y que usarás:

* `orden.nro` → `paymentNumber` (Id_Transaccion_Empresa).
* `orden.total` → `amount`.
* `cliente` de la orden → nombre, CI, teléfono, email.

### 5.1. Controlador de pago QR

Crea un controlador, por ejemplo `app/Http/Controllers/PagoQrController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PagoQrController extends Controller
{
    public function __construct(private PagoFacilService $pagoFacil)
    {
        $this->middleware(['auth', 'role:empleado,propietario']);
    }

    public function show(string $nro)
    {
        $orden = Orden::with('cliente')->findOrFail($nro);

        // 1) Login
        $token = $this->pagoFacil->login();

        // 2) Obtener método QR
        $paymentMethodId = $this->pagoFacil->getQrPaymentMethodId($token);

        // 3) Armar payload con datos de la orden
        $cliente = $orden->cliente;

        $payload = [
            'paymentMethod' => $paymentMethodId,
            'clientName'    => $cliente->nombre ?? 'Cliente',
            'documentType'  => 1,                        // 1 = CI (ver bibliografía si cambian)
            'documentId'    => $cliente->ci ?? '0',
            'phoneNumber'   => $cliente->telefono ?? '0',
            'email'         => $cliente->email ?? 'no-email@dummy.com',
            'paymentNumber' => $orden->nro,             // Id_Transaccion_Empresa
            'amount'        => $orden->total,
            'currency'      => 2,                       // 2 = BOB
            'clientCode'    => config('pagofacil.client_code'),
            'callbackUrl'   => config('pagofacil.callback_url'),
            'orderDetail'   => [
                [
                    'serial'   => 1,
                    'product'  => "Pago orden {$orden->nro}",
                    'quantity' => 1,
                    'price'    => $orden->total,
                    'discount' => 0,
                    'total'    => $orden->total,
                ],
            ],
        ];

        // 4) Generar QR
        $values = $this->pagoFacil->generateQrForOrder($token, $paymentMethodId, $payload);

        // values devuelve:
        // transactionId, paymentMethodTransactionId, status, expirationDate, qrBase64, ...
        // :contentReference[oaicite:9]{index=9}

        return Inertia::render('Pagos/MostrarQr', [
            'orden'           => $orden,
            'qrBase64'        => $values['qrBase64'] ?? null,
            'transactionId'   => $values['transactionId'] ?? null,
            'paymentStatus'   => $values['status'] ?? null,
            'expirationDate'  => $values['expirationDate'] ?? null,
        ]);
    }
}
```

### 5.2. Ruta

En `routes/web.php`:

```php
use App\Http\Controllers\PagoQrController;

Route::middleware(['auth'])->group(function () {
    Route::get('/ordenes/{nro}/pago-qr', [PagoQrController::class, 'show'])
        ->name('ordenes.pago-qr');
});
```

---

## 6. Vista Vue para mostrar el QR

Archivo sugerido: `resources/js/Pages/Pagos/MostrarQr.vue`:

```vue
<script setup>
const props = defineProps({
  orden: Object,
  qrBase64: String,
  transactionId: String,
  paymentStatus: Number,
  expirationDate: String,
});
</script>

<template>
  <div class="max-w-xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">
      Pago con QR – Orden {{ orden.nro }}
    </h1>

    <p class="mb-2">Total: <strong>{{ orden.total }}</strong> Bs.</p>
    <p class="mb-4">Transacción PagoFacil: <strong>{{ transactionId }}</strong></p>

    <div v-if="qrBase64" class="flex flex-col items-center gap-4">
      <img :src="`data:image/png;base64,${qrBase64}`" alt="QR PagoFacil" class="w-64 h-64" />
      <p class="text-sm text-gray-600">
        Escanea el QR con tu app bancaria para completar el pago.
      </p>
    </div>

    <div v-else class="text-red-600 mt-4">
      No se pudo generar el QR. Intenta nuevamente o contacta al administrador.
    </div>
  </div>
</template>
```

---

## 7. Callback: recibir notificación de PagoFacil

Según la documentación, si se especifica `callbackUrl`, PagoFacil enviará un `POST` a esa URL cuando el pago se realice. 

### 7.1. Estructura del callback

El cuerpo JSON que envía PagoFacil tiene la forma: 

```json
{
  "PedidoID": "Id de venta/factura del comercio",
  "Fecha": "YYYY-mm-dd",
  "Hora": "HH:ii:ss",
  "MetodoPago": "Nombre del medio de pago",
  "Estado": "Estado del pago"
}
```

En el grupo de soporte se indicó que los valores posibles de `Estado` son:

* **1** → En proceso / pendiente
* **2** → Pagado
* **4** → Anulado (no se recibió dinero o el QR caducó)
* **5** → Revisión (cuando no pudieron notificar correctamente por callback – por ejemplo URL incorrecta)

### 7.2. Respuesta obligatoria

Tu sistema **debe** responder con HTTP 200 + JSON: 

```json
{
  "error": 0,
  "status": 1,
  "message": "Notificación recibida correctamente",
  "values": true
}
```

Si no respondes así, o tu endpoint no existe / da error, PagoFacil pone la transacción en estado **5 – Revisión**, como le sucedió a varios estudiantes.

### 7.3. Implementación en Laravel

En `routes/web.php` (para el servidor público de Tecnoweb):

```php
use App\Http\Controllers\PagoQrCallbackController;

Route::post('/payment/callback', [PagoQrCallbackController::class, 'handle'])
    ->name('pagofacil.callback');
```

> Asegúrate de que esta ruta **coincida exactamente** con lo que pusiste en `PAGOFACIL_CALLBACK_URL` (`.../payment/callback`).

Controlador `app/Http/Controllers/PagoQrCallbackController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PagoQrCallbackController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Callback PagoFacil recibido', $request->all());

        $pedidoId = $request->input('PedidoID'); // Debe ser igual a orden.nro
        $estado   = (int) $request->input('Estado');

        if (!$pedidoId) {
            return response()->json([
                'error'   => 1,
                'status'  => 0,
                'message' => 'PedidoID faltante',
                'values'  => false,
            ], 400);
        }

        $orden = Orden::find($pedidoId);

        if (!$orden) {
            // No existe la orden, pero igual respondemos 200 para que la API no la deje en revisión
            Log::warning("Orden no encontrada para PedidoID={$pedidoId}");
        } else {
            if ($estado === 2) { // Pagado
                // Registrar pago (metodo = 'QR')
                Pago::create([
                    'orden_nro'  => $orden->nro,
                    'monto'      => $orden->total,
                    'metodo'     => 'QR',
                    'referencia' => "PagoFacil callback {$request->input('MetodoPago')}",
                ]);

                // Actualizar estado de la orden, saldo, etc. según tu lógica
            }
            // Estados 1,4,5 puedes loguearlos para auditoría
        }

        // Respuesta obligatoria
        return response()->json([
            'error'   => 0,
            'status'  => 1,
            'message' => 'Notificación recibida correctamente',
            'values'  => true,
        ]);
    }
}
```

---

## 8. Consulta manual del estado (`/query-transaction`)

Además del callback, puedes verificar el estado de una transacción en cualquier momento: 

Ejemplo de uso en un método de controlador:

```php
public function verificarEstado(string $nro, PagoFacilService $pf)
{
    $orden = Orden::findOrFail($nro);
    $token = $pf->login();

    $values = $pf->queryTransaction($token, [
        'companyTransactionId' => $orden->nro,
        // o 'pagofacilTransactionId' => '...'
    ]);

    // $values['paymentStatus'] contiene el estado numérico (1, 2, 4, 5)
    // $values['amount'], 'paymentDate', etc.

    // Aquí puedes mostrar la info en una vista o actualizar la BD
}
```

---

## 9. Errores frecuentes y cómo evitarlos

Basado en la experiencia del grupo de soporte:

1. **URL callback incorrecta**

   * Causa: ruta mal escrita, carpeta equivocada, falta `/payment/callback`, dominio erróneo.
   * Efecto: transacciones marcadas como **Estado 5 – Revisión**.
   * Solución:

     * Verificar que `PAGOFACIL_CALLBACK_URL` apunte a la ruta correcta en el servidor de Tecnoweb.
     * Probar esa URL con `curl` o Postman (simulando un `POST`) antes de enviarla a PagoFacil.
     * Asegurarse de que el controlador devuelva exactamente el JSON esperado con HTTP 200.

2. **No responder correctamente al callback**

   * Si tu endpoint devuelve HTML, error 500, o JSON con forma distinta, la API considera que no recibiste la notificación.
   * Solución: usar la estructura JSON de respuesta obligatoria (ver sección 7.2) y revisar logs (`storage/logs/laravel.log`).

3. **Token vencido**

   * No reutilizar un `accessToken` después de que expira (`expiresInMinutes` viene en la respuesta de login). 
   * Solución: guardar token en caché y renovarlo cuando venza o llamar `login()` cada vez que generes un QR.

4. **Montos o datos incoherentes**

   * `amount` debe coincidir con lo que le vas a cobrar al cliente.
   * `currency = 2` para BOB (según la documentación). 

5. **No guardar IDs importantes**

   * Debes guardar en tu BD:

     * `paymentNumber` (orden.nro, ya lo tienes).
     * `transactionId` de PagoFacil (por si necesitas `/query-transaction` o soporte).
   * Puedes almacenarlo en tu tabla `pago` (columna `referencia`) o crear una columna específica.

---

## 10. Checklist de pruebas

Antes de decir “ya está integrado”, realiza estas pruebas:

1. **Postman**

   * Probar `/login` con tus `tcTokenService` y `tcTokenSecret`.
   * Probar `/list-enabled-services` y confirmar que obtienes un método con `paymentMethodName` que contenga “QR”.
   * Probar `/generate-qr` con un monto pequeño (0.10 Bs) y verificar que devuelve `qrBase64` y `transactionId`.
   * Probar `/query-transaction` con `companyTransactionId` y/o `pagofacilTransactionId`.

2. **Laravel – Dev local**

   * Llamar al endpoint `GET /ordenes/{nro}/pago-qr`.
   * Ver que se muestre el QR en la página.
   * Escanear desde alguna app bancaria (en modo real o entorno de pruebas si lo hay).

3. **Callback**

   * Verificar que al pagar el QR:

     * Llegue una petición POST a `/payment/callback` en el servidor de Tecnoweb.
     * Tu sistema registre el pago en la BD.
     * Se responda el JSON obligatorio.
   * Revisar con `Log::info` y en el archivo de log.

4. **Consulta manual**

   * Si por alguna razón el callback no llega, usar `/query-transaction` para confirmar el estado y actualizar la orden.

---

Con esta guía ya tienes una integración **alineada 100% con la documentación oficial MasterQR** y adaptada al flujo de **órdenes de la Lavandería BELÉN**, evitando los errores más comunes (sobre todo la URL callback y la respuesta al POST de PagoFacil).

```
```
