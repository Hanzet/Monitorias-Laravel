# Configuración de Laravel

## 🎯 Información General

-   **Versión**: Laravel 11.x
-   **PHP**: 8.2+
-   **Base de datos**: MySQL 8.0
-   **Cache**: Redis
-   **Autenticación**: Laravel Sanctum

## 📁 Estructura del Proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php      # Controlador de autenticación
│   │   ├── UserController.php      # Controlador de usuarios
│   │   └── Controller.php          # Controlador base
│   ├── Middleware/
│   │   └── ApiAuthentication.php   # Middleware de autenticación API
│   └── Requests/
│       ├── LoginRequest.php        # Validación de login
│       └── RegisterRequest.php     # Validación de registro
├── Models/
│   └── User.php                    # Modelo de usuario
└── Providers/
    ├── AppServiceProvider.php      # Proveedor de servicios
    └── TelescopeServiceProvider.php # Proveedor de Telescope

config/
├── app.php                         # Configuración principal
├── auth.php                        # Configuración de autenticación
├── database.php                    # Configuración de base de datos
└── sanctum.php                     # Configuración de Sanctum

database/
├── migrations/                     # Migraciones de base de datos
├── seeders/                        # Seeders para datos de prueba
└── factories/                      # Factories para testing

routes/
├── api.php                         # Rutas de la API
└── web.php                         # Rutas web

storage/
├── logs/                           # Logs de la aplicación
└── app/                            # Archivos de la aplicación
```

## ⚙️ Configuración del Entorno

### Archivo .env

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:tu_clave_generada_aqui
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=monitorias
DB_USERNAME=monitorias
DB_PASSWORD=root

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Configuración de Base de Datos

**Archivo: `config/database.php`**

```php
'mysql' => [
    'driver' => 'mysql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

### Configuración de Autenticación

**Archivo: `config/auth.php`**

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

## 🔐 Autenticación con Sanctum

### Configuración de Sanctum

**Archivo: `config/sanctum.php`**

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),

'guard' => ['web'],

'expiration' => null,

'middleware' => [
    'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
],
```

### Middleware de Autenticación

**Archivo: `app/Http/Middleware/ApiAuthentication.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json([
                'message' => 'Unauthenticated',
                'status' => 401
            ], 401);
        }

        return $next($request);
    }
}
```

## 🗄️ Modelos

### Modelo User

**Archivo: `app/Models/User.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

## 🎮 Controladores

### AuthController

**Archivo: `app/Http/Controllers/AuthController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
            'status' => 201
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
                'status' => 401
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'status' => 200
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
            'status' => 200
        ]);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'status' => 200
        ]);
    }
}
```

### UserController

**Archivo: `app/Http/Controllers/UserController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json([
            'users' => $users,
            'status' => 200
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'user' => $user,
            'status' => 200
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
            'status' => 200
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
            'status' => 200
        ]);
    }
}
```

## ✅ Validación de Requests

### LoginRequest

**Archivo: `app/Http/Requests/LoginRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'El email es requerido',
            'email.email' => 'El email debe ser válido',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
        ];
    }
}
```

### RegisterRequest

**Archivo: `app/Http/Requests/RegisterRequest.php`**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El email es requerido',
            'email.email' => 'El email debe ser válido',
            'email.unique' => 'El email ya está registrado',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ];
    }
}
```

## 🛣️ Rutas

### Rutas de la API

**Archivo: `routes/api.php`**

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Rutas de usuarios
    Route::apiResource('users', UserController::class);
});

// Ruta de prueba
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'status' => 200
    ]);
});
```

## 🗄️ Migraciones

### Migración de Usuarios

**Archivo: `database/migrations/0001_01_01_000000_create_users_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
```

### Migración de Personal Access Tokens

**Archivo: `database/migrations/2025_07_24_021655_create_personal_access_tokens_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
```

## 🌱 Seeders

### UserSeeder

**Archivo: `database/seeders/UserSeeder.php`**

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
```

## 🔧 Comandos de Artisan

### Comandos Básicos

```bash
# Generar clave de aplicación
php artisan key:generate

# Limpiar caché
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Ejecutar migraciones
php artisan migrate
php artisan migrate:status
php artisan migrate:rollback

# Ejecutar seeders
php artisan db:seed
php artisan db:seed --class=UserSeeder

# Ver rutas
php artisan route:list

# Crear controlador
php artisan make:controller UserController

# Crear modelo
php artisan make:model User -m

# Crear request
php artisan make:request LoginRequest

# Crear middleware
php artisan make:middleware ApiAuthentication
```

### Comandos de Desarrollo

```bash
# Ejecutar Tinker
php artisan tinker

# Ver configuración
php artisan config:show app
php artisan config:show database

# Optimizar aplicación
php artisan optimize
php artisan config:cache
php artisan route:cache

# Ver logs
tail -f storage/logs/laravel.log
```

## 🧪 Testing

### Configuración de Testing

**Archivo: `phpunit.xml`**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

### Ejecutar Tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter=AuthTest

# Ejecutar tests con cobertura
php artisan test --coverage
```

## 📊 Logging

### Configuración de Logs

**Archivo: `config/logging.php`**

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'ignore_exceptions' => false,
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

### Ver Logs

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver logs de errores
grep "ERROR" storage/logs/laravel.log

# Limpiar logs
> storage/logs/laravel.log
```

## 🔍 Debugging

### Configuración de Debug

```php
// En .env
APP_DEBUG=true
LOG_LEVEL=debug

// En config/app.php
'debug' => env('APP_DEBUG', false),
```

### Herramientas de Debug

```bash
# Ejecutar Tinker para debugging
php artisan tinker

# Ver variables de entorno
php artisan env

# Ver configuración
php artisan config:show
```

## 🚀 Optimización

### Para Producción

```bash
# Optimizar autoloader
composer install --optimize-autoloader --no-dev

# Cachear configuración
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar aplicación
php artisan optimize
```

### Configuración de Caché

```php
// En .env
CACHE_DRIVER=redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```
