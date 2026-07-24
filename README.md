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

---

# 🔗 Repositorios

| Proyecto | Descripción |
|----------|-------------|
| **guanajuato-api** | Backend Laravel |
| **guanajuato-flutter** | Aplicación Flutter |

---