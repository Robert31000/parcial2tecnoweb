# 🧾 Integración PagoFacil QR - Lavandería BELÉN

## 📋 Resumen

Este sistema integra la API de **PagoFacil MasterQR** para pagos con QR en la gestión de órdenes de la lavandería.

### Características principales:
- ✅ Generación automática de códigos QR para pagos
- ✅ Monto de prueba configurable (0.10 Bs por defecto)
- ✅ Registro automático del monto real de la orden
- ✅ Verificación manual y automática del estado de pago
- ✅ Callback para confirmación automática (requiere URL pública)
- ✅ Logs detallados para debugging

---

## 🚀 Despliegue Rápido

### 1. Configurar variables de entorno

Copia `.env.example` a `.env` y ajusta las siguientes variables según tu entorno:

#### Para Desarrollo Local:
```env
PAGOFACIL_CALLBACK_URL=http://localhost:8000/payment/callback
PAGOFACIL_RETURN_URL=http://localhost:8000/ordenes
```

#### Para DigitalOcean App Platform:
```env
PAGOFACIL_CALLBACK_URL=https://tu-app-name.ondigitalocean.app/payment/callback
PAGOFACIL_RETURN_URL=https://tu-app-name.ondigitalocean.app/ordenes
APP_URL=https://tu-app-name.ondigitalocean.app
```

#### Para Servidor Tecnoweb:
```env
PAGOFACIL_CALLBACK_URL=https://www.tecnoweb.org.bo/inf513/grupo26sc/lavanderia/payment/callback
PAGOFACIL_RETURN_URL=https://www.tecnoweb.org.bo/inf513/grupo26sc/lavanderia/ordenes
APP_URL=https://www.tecnoweb.org.bo/inf513/grupo26sc/lavanderia
```

### 2. Instalar dependencias y compilar assets

```bash
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### 3. Configurar base de datos

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔧 Cómo Funciona

### Flujo de Pago con QR

1. **Usuario accede a una orden** con saldo pendiente
2. **Hace clic en "Pagar con QR"** → Se genera QR con monto de **0.10 Bs** (prueba)
3. **Cliente escanea el QR** con su app bancaria
4. **Cliente paga 0.10 Bs** en la app bancaria
5. **Sistema verifica el pago** (automático cada 10s o manual con botón)
6. **Cuando confirma:** Se registra el **monto real** de la orden en la BD
7. **Orden actualizada** con el pago completo

### Modos de Verificación

#### 🔄 Verificación Automática (cada 10 segundos)
- El frontend consulta automáticamente el estado del pago
- No requiere URL pública
- ✅ **Funciona en desarrollo local**

#### 📞 Callback Automático (requiere URL pública)
- PagoFacil envía POST cuando el pago se confirma
- Más rápido y confiable
- ⚠️ **Solo funciona en producción** (URL pública)

---

## 🧪 Probar en Local

### 1. Iniciar servidor
```bash
php artisan serve
```

### 2. Navegar a una orden
- Ve a `/ordenes`
- Selecciona una orden con saldo pendiente
- Clic en "📱 Pagar con QR"

### 3. Simular pago
- Escanea el QR con tu app bancaria
- Paga **0.10 Bs**
- Vuelve a la página y haz clic en "Verificar Pago"

### 4. Verificar resultado
- Debería aparecer "✅ Pago confirmado exitosamente"
- El pago se registra con el **monto real** de la orden
- La orden actualiza su estado a "PAGADA" si está completamente pagada

---

## 📝 Logs y Debugging

Los logs se guardan en `storage/logs/laravel.log`:

```php
// Buscar en los logs:
'Callback PagoFacil recibido'  // Cuando llega el callback
'PagoFacil login response'     // Respuesta del login
'PagoFacil generate-qr response' // Respuesta al generar QR
'PagoFacil query-transaction response' // Respuesta al consultar estado
```

Para ver logs en tiempo real:
```bash
tail -f storage/logs/laravel.log
```

---

## ⚠️ Problemas Comunes

### ❌ "Error en login PagoFacil"
- **Causa:** Tokens incorrectos
- **Solución:** Verifica `PAGOFACIL_TOKEN_SERVICE` y `PAGOFACIL_TOKEN_SECRET` en `.env`

### ❌ "No se encontró método QR habilitado"
- **Causa:** Los tokens no tienen acceso a QR
- **Solución:** Contacta con PagoFacil para habilitar el servicio QR

### ❌ Callback no llega (Estado 5 - Revisión)
- **Causa:** URL callback incorrecta o no accesible
- **Solución:** 
  1. Verifica que `PAGOFACIL_CALLBACK_URL` sea una URL pública válida
  2. Prueba la URL con `curl -X POST https://tu-url/payment/callback`
  3. En local, usa solo la verificación manual (botón)

### ❌ "Pago en proceso" perpetuo
- **Causa:** El pago no se completó o el QR expiró
- **Solución:** 
  1. Verifica en tu app bancaria si el pago se realizó
  2. Espera unos segundos y vuelve a verificar
  3. Si persiste, genera un nuevo QR

---

## 🔐 Seguridad

- ✅ Tokens de PagoFacil **nunca** se exponen al frontend
- ✅ Callback valida que el `PedidoID` corresponda a una orden existente
- ✅ No se permite duplicar pagos (verifica si ya existe)
- ✅ El monto del QR (0.10) y el monto registrado (real) están separados
- ✅ Logs detallados para auditoría

---

## 📊 Estados de Pago

| Estado | Descripción |
|--------|-------------|
| **1** | En proceso / pendiente |
| **2** | ✅ **Pagado** (se registra el pago) |
| **4** | Anulado / QR expirado |
| **5** | En revisión (callback falló) |

---

## 🛠️ Mantenimiento

### Limpiar caché del token
```bash
php artisan cache:clear
```

### Ver logs de PagoFacil
```bash
grep "PagoFacil" storage/logs/laravel.log
```

### Probar API manualmente con Postman
Importa la colección de PagoFacil y prueba:
1. POST `/login` → obtener token
2. POST `/list-enabled-services` → verificar QR habilitado
3. POST `/generate-qr` → generar QR de prueba

---

## 📞 Soporte

Si tienes problemas:

1. **Revisa los logs** en `storage/logs/laravel.log`
2. **Verifica las variables** de entorno en `.env`
3. **Consulta la documentación** oficial de PagoFacil
4. **Contacta con el equipo** de soporte de PagoFacil

---

## 📚 Archivos Importantes

- `config/pagofacil.php` - Configuración centralizada
- `app/Services/PagoFacilService.php` - Lógica de API
- `app/Http/Controllers/PagoQrController.php` - Generación y verificación de QR
- `app/Http/Controllers/PagoQrCallbackController.php` - Manejo de callbacks
- `resources/js/Pages/Pagos/MostrarQr.vue` - Interfaz de usuario
- `routes/web.php` - Rutas del módulo de pagos

---

¡Listo para producción! 🎉
