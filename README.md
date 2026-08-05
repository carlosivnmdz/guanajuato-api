# 🚀 Guanajuato API

Backend REST desarrollado con **Laravel 12** para la aplicación **Guanajuato Flutter**.

La API proporciona autenticación mediante OTP, consulta de departamentos, productos y perfil de usuario consumiendo el ERP Passport.

---

# 📌 Características

- ✅ API REST
- ✅ Laravel 12
- ✅ Laravel Sanctum
- ✅ Autenticación OTP
- ✅ Registro de usuarios
- ✅ Inicio de sesión
- ✅ Consulta de productos
- ✅ Consulta de departamentos
- ✅ Búsqueda de productos
- ✅ Integración con Passport ERP

---

# 🛠 Tecnologías

| Tecnología | Versión |
|------------|----------|
| PHP | 8.3 |
| Laravel | 12 |
| MySQL | 8 |
| Sanctum | ✔ |
| Gmail SMTP | OTP por correo |
| Passport ERP | API de productos |

---

# 📂 Estructura

```
app/
├── Http/
│   └── Controllers/
│       └── Api/
│
├── Mail/
├── Models/
├── Services/
│
routes/
config/
database/
```

---

# 🔐 Autenticación

## Registro

```
POST /api/auth/register
```

## Verificar OTP

```
POST /api/auth/verify-otp
```

## Login

```
POST /api/auth/login
```

## Verificar OTP de Login

```
POST /api/auth/login/verify
```

## Usuario autenticado

```
GET /api/auth/me
```

## Cerrar sesión

```
POST /api/auth/logout
```

---

# 📦 Productos

## Listado

```
GET /api/products
```

Parámetros soportados

| Parámetro | Descripción |
|-----------|-------------|
| page | Página |
| per_page | Productos por página |
| department | Departamento |

---

## Buscar

```
GET /api/products/search
```

Ejemplo

```
/api/products/search?q=pollo
```

---

## Departamentos

```
GET /api/products/departments
```

---

## Detalle

```
GET /api/products/{itemId}
```

---

## Limpiar caché

```
POST /api/products/cache/clear
```

---

# 💳 Wallet (tarjeta digital)

Endpoints para agregar la tarjeta digital del cliente a Apple Wallet /
Google Wallet. Requieren sesión (`auth:sanctum`).

## Pase de Apple Wallet

```
GET /api/wallet/apple-pass
```

## Pase de Google Wallet

```
GET /api/wallet/google-pass
```

### Estado actual

La generación de pases todavía no está implementada — cada endpoint
depende de `WalletService`, que hoy solo valida si la plataforma está
habilitada (`config/wallet.php` / `APPLE_WALLET_ENABLED`,
`GOOGLE_WALLET_ENABLED`) y, si lo está, lanza una excepción "pendiente
de implementar". Mientras eso no cambie, ambos endpoints responden:

- `503` con `{"success": false, "message": "..."}` si la plataforma
  está deshabilitada o si aún no se implementó la generación del pase.

Falta por hacer:

- **Apple**: armar el `.pkpass` (pass.json + manifest + firma PKCS#7
  con el certificado Pass Type ID y el certificado WWDR de Apple) a
  partir del cliente autenticado, subirlo y devolver su URL pública.
- **Google**: armar el objeto de loyalty pass, firmarlo como JWT con
  el service account, y devolver `https://pay.google.com/gp/v/save/<jwt>`.

Variables de entorno relacionadas (ver `.env.example`):

```
APPLE_WALLET_ENABLED=
APPLE_PASS_TYPE_IDENTIFIER=
APPLE_TEAM_IDENTIFIER=
APPLE_PASS_CERTIFICATE_PATH=
APPLE_PASS_CERTIFICATE_PASSWORD=
APPLE_WWDR_CERTIFICATE_PATH=

GOOGLE_WALLET_ENABLED=
GOOGLE_WALLET_ISSUER_ID=
GOOGLE_WALLET_SERVICE_ACCOUNT_PATH=
```

---

# 🚀 Instalación

## Clonar

```bash
git clone https://github.com/carlosivnmdz/guanajuato-api.git

cd guanajuato-api
```

## Instalar dependencias

```bash
composer install
```

## Configurar variables

```bash
cp .env.example .env
```

## Generar llave

```bash
php artisan key:generate
```

## Ejecutar migraciones

```bash
php artisan migrate
```

## Iniciar servidor

```bash
php artisan serve
```

---

# 🔄 Sincronización de clientes CATAPULT → Laravel

## Por qué existe

El login solo busca usuarios en la tabla local `users`. Si un cliente
se dio de alta directo en tienda (CATAPULT), y nunca por la app, no
tiene fila local — no puede iniciar sesión aunque sea cliente real.
Este sync resuelve eso: refleja hacia `users` los clientes que ya
existen en CATAPULT.

## Cómo se dispara

No usa cron. El servidor puede no tener acceso a crontab, y en
desarrollo local tampoco hay forma de que un cron externo le llegue.
En vez de eso, el middleware `MaybeSyncCatapultCustomers` (colgado
del grupo `api` en `bootstrap/app.php`) se aprovecha del tráfico
normal: en cada request revisa si ya pasaron 20 minutos desde el
último intento (usando un lock en cache) y, si sí, despacha
`SyncCatapultCustomersJob` con `->afterResponse()` — corre después de
responderle al cliente, así que no le agrega latencia a nada.

## Qué hace, paso a paso

1. `CustomerService::pullChanges()` — `GET /Customer` en CATAPULT.
   Trae todos los clientes la primera vez; después, solo los
   modificados desde el último sync (`modifiedSince`, guardado en
   cache).
2. `CustomerSyncService::sync()` — por cada cliente que regresa:
   - Si ya existe localmente (por `customer_id`), **solo actualiza
     nombre, apellido, fecha de nacimiento y país**.
   - Si no existe, crea la fila nueva, y en ese caso sí toma el
     correo/teléfono que traiga CATAPULT como punto de partida.

## Importante: nunca toca correo/teléfono de un usuario que ya existe

`billToEmailAddress`/`billToPhoneNumber` (los campos que expone
`GET /Customer`) casi siempre vienen vacíos, y no son lo mismo que el
correo/teléfono que la app usa para el login por OTP. Una versión
anterior de este sync sí los sobreescribía en cada corrida y le borró
el correo a clientes que ya se habían registrado por la app — bug ya
corregido. Correo y teléfono solo se escriben una vez, al crear el
registro por primera vez; nunca se vuelven a tocar después.

## De solo lectura hacia CATAPULT

Todo el flujo usa únicamente `GET /Customer`. Nunca llama al
`POST /batch/customerMaintenance` (eso solo lo usan el registro y la
edición de perfil, que sí escriben hacia CATAPULT). El sync jamás
modifica nada del lado de CATAPULT.

## Forzar un sync manual (para probar)

```bash
php artisan tinker
```

```php
app(App\Services\Passport\CustomerSyncService::class)->sync();
```

## Archivos relevantes

```
app/Http/Middleware/MaybeSyncCatapultCustomers.php
app/Jobs/SyncCatapultCustomersJob.php
app/Services/Passport/CustomerSyncService.php
app/Services/Passport/CustomerService.php (método pullChanges)
```

---

# ⚙ Variables de entorno

El proyecto requiere configurar:

```
APP_NAME=
APP_ENV=

DB_CONNECTION=
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=

PASSPORT_API_URL=
PASSPORT_API_KEY=
```
---

# 🏗 Arquitectura

```
Flutter
      │
      │ HTTPS
      ▼
Laravel API
      │
      ├── AuthController
      ├── ProductController
      │
      ▼
Passport ERP API
      │
      ▼
ERP
```

---

# 📅 Roadmap

## v0.1.0

- ✅ Proyecto Laravel

## v0.2.0

- ✅ Autenticación OTP

## v0.3.0

- ✅ Productos

## v0.4.0

- ✅ Departamentos

## v0.5.0

- ✅ Integración Passport ERP

## v0.6.0

- 🚧 Wallet (Apple Wallet / Google Wallet) — endpoints y config listos,
  generación de pases pendiente

---

# 🔗 Repositorios

| Proyecto | Descripción |
|----------|-------------|
| **guanajuato-api** | Backend Laravel |
| **guanajuato-flutter** | Aplicación Flutter |

---