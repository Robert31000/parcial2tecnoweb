## 2️⃣ Guía completa para el proyecto Laravel + Inertia + Vue (para pegar en un `.md`)

Te dejo ahora la guía larga, pensada para que la pegues en un archivo como `docs/guia_proyecto.md` dentro de Laravel.

````md
# 🧺 Sistema de Gestión para Lavandería BELÉN  
Guía de Arquitectura y Desarrollo (Laravel + Inertia + Vue + PostgreSQL)

## 1. Descripción general

El sistema de gestión para **Lavandería BELÉN** es una aplicación web desarrollada con:

- **Laravel** (backend, API, lógica de negocio)
- **Inertia.js** (puente entre Laravel y Vue)
- **Vue 3** (frontend reactivo tipo SPA)
- **PostgreSQL** (base de datos relacional)
- Autenticación con **Laravel Breeze**
- Pagos con **EFECTIVO** y **QR** (integración futura con PagoFácil)

El objetivo es gestionar:

- Usuarios del sistema (propietario, empleados)
- Clientes y proveedores
- Servicios de lavandería
- Órdenes y detalles de servicio
- Procesos de lavado / secado / planchado
- Equipos y mantenimientos
- Insumos e inventario
- Pagos (contado y crédito)
- Estadísticas e informes

Además, cumplir los requisitos del proyecto final de Tecnología Web:

1. Diseño y navegación
2. Roles de acceso
3. Menú dinámico (desde BD)
4. MVC–MVVM (Laravel–Inertia–Vue)
5. Estilos con temas y accesibilidad
6. Validación en español
7. Contador de visitas por página
8. Estadísticas de negocio y acceso
9. Búsqueda de información
10. Pagos electrónicos (QR)

---

## 2. Base de datos (resumen)

La BD está en PostgreSQL (por ejemplo, `db_grupo26sc`) y se crea con el script SQL definido previamente.  
Tablas principales de negocio:

- `usuario`, `empleado`, `propietario`
- `cliente`, `proveedor`
- `servicio`, `promocion`, `promocion_servicio`
- `orden`, `orden_detalle`
- `equipo`, `mantenimiento`
- `orden_proceso`, `proceso_insumo`
- `insumo`, `inventario`
- `pago`

Tablas de apoyo al sitio web:

- `menu_item` → definición de opciones de menú por tipo de usuario
- `pagina` → contador de visitas por ruta

> Importante:  
> - La PK de `orden` es `nro` (string, no autoincremental).  
> - La PK de `equipo` es `codigo`.  
> - La PK de `insumo` es `codigo`.  

Esto debe reflejarse correctamente en los modelos Eloquent.

---

## 3. Creación del proyecto Laravel

### 3.1. Crear el proyecto

```bash
composer create-project laravel/laravel lav_belen
cd lav_belen
````

### 3.2. Instalar Laravel Breeze con Inertia + Vue

```bash
composer require laravel/breeze --dev
php artisan breeze:install vue
npm install
npm run dev
```

Esto genera:

* Rutas de autenticación
* Controladores de login/registro
* Vistas base con Inertia + Vue
* Layout principal

---

## 4. Configuración de conexión a PostgreSQL

En el archivo `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_grupo26sc
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

> La BD `db_grupo26sc` debe existir y tener el script SQL ejecutado desde pgAdmin.

---

## 5. Autenticación y modelo `usuario`

### 5.1. Usar la tabla `usuario` de la BD

En lugar de `users`, el sistema usa la tabla `usuario` con los campos:

* `id` (PK, SERIAL)
* `nombre`
* `telefono`
* `email`
* `password`
* `tipo_usuario` (`'propietario'` o `'empleado'`)
* `estado`

### 5.2. Modelo Eloquent

Renombrar `app/Models/User.php` a `app/Models/Usuario.php` y ajustar:

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuario';
    protected $primaryKey = 'id';
    public $timestamps = false; // la tabla no tiene created_at/updated_at

    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'password',
        'tipo_usuario',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
```

### 5.3. Ajustar `config/auth.php`

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\Usuario::class,
    ],
],
```

### 5.4. Campos de registro

Adaptar los controladores/views de Breeze para que al registrar un usuario:

* Se cree un registro en `usuario`
* Se asigne `tipo_usuario` (por ejemplo, `empleado` por defecto; el `propietario` se crea manualmente o por seed)

---

## 6. Modelos Eloquent principales

Por cada tabla de negocio, crear un modelo Eloquent en `app/Models`.
Ejemplos clave:

### 6.1. Modelo `Orden`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $table = 'orden';
    protected $primaryKey = 'nro';
    public $incrementing = false; // PK string
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'nro', 'fecha_recepcion', 'fecha_listo', 'fecha_entrega',
        'estado', 'forma_pago', 'fecha_vencimiento',
        'subtotal', 'descuento', 'total',
        'cliente_id', 'empleado_id', 'observaciones',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function detalles()
    {
        return $this->hasMany(OrdenDetalle::class, 'orden_nro', 'nro');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'orden_nro', 'nro');
    }
}
```

### 6.2. Modelo `Equipo`

```php
class Equipo extends Model
{
    protected $table = 'equipo';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public function procesos()
    {
        return $this->hasMany(OrdenProceso::class, 'equipo_codigo', 'codigo');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'equipo_codigo', 'codigo');
    }
}
```

### 6.3. Modelo `Insumo`

```php
class Insumo extends Model
{
    protected $table = 'insumo';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
```

(Se pueden definir modelos similares para el resto: `Cliente`, `Proveedor`, `Servicio`, `Promocion`, `Inventario`, `Pago`, etc.)

---

## 7. Roles de acceso (propietario / empleado)

Los roles de negocio se manejan con la columna `tipo_usuario` en `usuario`.

### 7.1. Middleware `TipoUsuario`

Crear un middleware para restringir por tipo:

```bash
php artisan make:middleware TipoUsuario
```

```php
// app/Http/Middleware/TipoUsuario.php
public function handle($request, Closure $next, ...$tipos)
{
    $user = auth()->user();

    if (!$user || !in_array($user->tipo_usuario, $tipos)) {
        abort(403, 'Acceso no autorizado.');
    }

    return $next($request);
}
```

Registrar en `app/Http/Kernel.php`:

```php
'role' => \App\Http\Middleware\TipoUsuario::class,
```

Usar en rutas:

```php
Route::middleware(['auth', 'role:propietario'])->group(function () {
    // rutas solo para propietario
});

Route::middleware(['auth', 'role:empleado,propietario'])->group(function () {
    // rutas para ambos
});
```

Con esto se cumple el requisito de “Dos roles de acceso al negocio”.

---

## 8. Menú dinámico con `menu_item`

### 8.1. Tabla `menu_item` (ya creada en SQL)

Campos:

* `nombre`: texto visible (ej. “Órdenes”)
* `ruta`: URL o nombre de ruta Inertia (ej. `/ordenes`)
* `tipo_usuario`: `'propietario'` o `'empleado'`
* `orden`: posición
* `activo`: booleano

### 8.2. Modelo

```php
class MenuItem extends Model
{
    protected $table = 'menu_item';
    public $timestamps = false;
    protected $fillable = ['nombre','ruta','icono','tipo_usuario','orden','activo'];
}
```

### 8.3. Cargar menú según usuario

En un `Composer` de vistas o en un controlador base, cargar el menú:

```php
$menu = MenuItem::where('tipo_usuario', auth()->user()->tipo_usuario)
                ->where('activo', true)
                ->orderBy('orden')
                ->get();
```

Pasar `$menu` a Inertia (por ejemplo, desde un middleware que comparte datos globales).

En Vue (layout principal), recorrer `menu` y dibujar los enlaces.
Con esto se cumple **“Menú Dinámico (uso de base de datos)”**.

---

## 9. Contador de visitas por página (`pagina`)

### 9.1. Tabla `pagina` (ya está en SQL)

* `ruta`: cadena única (ej. `/ordenes`)
* `visitas_totales`: entero

### 9.2. Modelo

```php
class Pagina extends Model
{
    protected $table = 'pagina';
    public $timestamps = false;
    protected $fillable = ['nombre','ruta','visitas_totales'];
}
```

### 9.3. Helper para registrar visita

Crear un método reutilizable, por ejemplo en un trait o helper:

```php
use App\Models\Pagina;

function registrarVisita(string $ruta, string $nombre)
{
    $pagina = Pagina::firstOrCreate(
        ['ruta' => $ruta],
        ['nombre' => $nombre]
    );
    $pagina->increment('visitas_totales');
    return $pagina->visitas_totales;
}
```

En cada controlador de página, llamar a la función y pasar el valor a la vista:

```php
public function index()
{
    $visitas = registrarVisita('/ordenes', 'Listado de órdenes');

    return Inertia::render('Ordenes/Index', [
        'visitas' => $visitas,
        // otros datos...
    ]);
}
```

En el `footer` del layout Vue mostrar:

```vue
<footer class="...">
  <span>Visitas a esta página: {{ props.visitas }}</span>
</footer>
```

Con esto se cumple el punto **7. “Contador por cada página”**.

---

## 10. Temas y accesibilidad (punto 5)

Se manejará con **CSS + LocalStorage**, sin tablas adicionales.

Requisitos:

* Estilo único general.
* Al menos 3 temas:

  * Ejemplo: `kids`, `young`, `classic`
* Modo día/noche (según horario del cliente o botón)
* Accesibilidad:

  * Cambio de tamaño de letra
  * Alto contraste

### 10.1. Estrategia

* En Vue, tener un `ThemeStore` (o composable) que maneje:

  * `theme` (`'kids' | 'young' | 'classic'`)
  * `mode` (`'day' | 'night' | 'auto'`)
  * `fontSize` (`'normal' | 'large' | 'xlarge'`)
  * `highContrast` (boolean)

* Guardar esos valores en `localStorage`:

```js
localStorage.setItem('lav_theme', JSON.stringify({
  theme, mode, fontSize, highContrast
}));
```

* Al cargar la app, leer `localStorage` y aplicar clases al `<body>`.

En CSS:

```css
body.theme-kids { /* colores y tipografías */ }
body.theme-young { ... }
body.theme-classic { ... }

body.high-contrast { filter: contrast(120%); }

body.font-large { font-size: 1.1rem; }
body.font-xlarge { font-size: 1.25rem; }
```

Esto cumple el punto 5 sin complicar el modelo de datos.

---

## 11. Validación de formularios (en español)

* Usar **Form Requests** de Laravel.
* Mensajes de validación personalizados en español (`resources/lang/es/validation.php` o dentro de cada FormRequest).

Ejemplo rápido:

```bash
php artisan make:request StoreOrdenRequest
```

En `rules()`:

```php
return [
    'cliente_id'   => ['required','exists:cliente,id'],
    'empleado_id'  => ['required','exists:empleado,id'],
    'forma_pago'   => ['required','in:CONTADO,CREDITO'],
    // ...
];
```

En `messages()`:

```php
return [
    'cliente_id.required' => 'Debe seleccionar un cliente.',
    'forma_pago.in'       => 'La forma de pago no es válida.',
];
```

---

## 12. Búsquedas de información

Para cumplir el punto 9:

* En las vistas de listas (órdenes, clientes, servicios, etc.) agregar un campo de búsqueda.
* El controlador debe recibir parámetros `request('q')`, `request('estado')`, `request('fecha_desde')`, etc., y filtrar con Eloquent.

Ejemplo para órdenes:

```php
$query = Orden::query();

if ($request->filled('nro')) {
    $query->where('nro', 'ILIKE', '%'.$request->nro.'%');
}

if ($request->filled('cliente')) {
    $query->whereHas('cliente', function($q) use ($request) {
        $q->where('nombre', 'ILIKE', '%'.$request->cliente.'%');
    });
}

$ordenes = $query->orderByDesc('fecha_recepcion')->paginate(10);
```

---

## 13. Estadísticas del negocio (punto 8)

Se pueden construir endpoints tipo `ReportController` que calculen:

* Ingresos por periodo:

  * Suma de `pago.monto` agrupado por mes.
* Órdenes por estado:

  * Conteo de `orden` por `estado`.
* Insumos más usados:

  * Suma de `proceso_insumo.cantidad` agrupado por `insumo_codigo`.
* Consumo de agua y energía por equipo:

  * Suma de `orden_proceso.kwh_consumidos` y `agua_litros_consumidos`.

Los resultados se envían a páginas Vue con gráficos (por ejemplo, usando Chart.js o ApexCharts).

---

## 14. Pagos y pagos electrónicos (punto 10)

En la tabla `pago`:

* `metodo` ∈ `('EFECTIVO','QR')`
* `monto` (NUMERIC)
* `orden_nro` FK a `orden`

### 14.1. Pagos al contado / crédito

* Se permite registrar **varios pagos** por una misma orden (plan de pagos / abonos).
* El saldo se calcula en Laravel:

```php
$pagado = $orden->pagos()->sum('monto');
$saldo = $orden->total - $pagado;
```

### 14.2. Integración QR (PagoFácil)

A nivel de guía:

* Crear un servicio `PagoQrService` que:

  * Haga un POST a la API de PagoFácil con `monto`, `detalle`, `nro_orden`.
  * Reciba la URL o imagen del QR.
* Mostrar el QR en la vista de pago.
* Una vez confirmado el pago (mediante callback o consulta a la API), registrar un `pago` con:

  * `metodo = 'QR'`
  * `monto` igual al monto cobrado
  * `referencia` con el ID de transacción externo.

Esto cumple la parte de “pagos electrónicos” del enunciado.

---

## 15. Checklist vs Requisitos del Proyecto Final

1. **Diseño y navegación**

   * Layout global con Vue + Inertia
   * Menú lateral/superior generado desde BD

2. **Dos roles de acceso al negocio**

   * `usuario.tipo_usuario` = `'propietario'` / `'empleado'`
   * Middleware `role`

3. **Menú dinámico (BD)**

   * Tabla `menu_item`
   * Menú filtrado por `tipo_usuario`

4. **MVC–MVVM (Laravel–Inertia)**

   * Laravel: modelos, controladores, rutas
   * Inertia + Vue: vistas, componentes y estado

5. **Estilo único + 3 temas + accesibilidad**

   * CSS + clases + LocalStorage
   * Cambios en tema, tamaño de fuente, contraste

6. **Validar entradas (mensajes en español)**

   * FormRequests + mensajes personalizados

7. **Contador por cada página**

   * Tabla `pagina`
   * Helper `registrarVisita()`
   * Valor mostrado en el footer

8. **Estadísticas del negocio y acceso**

   * Reportes sobre `orden`, `pago`, `inventario`, `pagina`

9. **Búsqueda de información**

   * Filtrado en listas de negocio (órdenes, clientes, servicios, etc.)

10. **Pagos electrónicos**

* Tabla `pago` con `metodo = 'EFECTIVO' | 'QR'`
* Integración futura con PagoFácil para generar/eliminar QR y registrar pago.

---

Con esta guía, el proyecto queda **alineado con la BD de la lavandería** y con todos los requisitos de la materia.
Este archivo `.md` sirve como **documento maestro** para ti, para tu docente y también como contexto para herramientas como Copilot al momento de generar código.

```
```
