# Guía de Desarrollo

## 🚀 Flujo de Desarrollo

### 1. Configuración del Entorno de Desarrollo

#### Estructura de Directorios Recomendada

```
monitorias/
├── app/                    # Lógica de la aplicación
├── config/                 # Configuraciones
├── database/               # Migraciones, seeders, factories
├── docs/                   # Documentación
├── routes/                 # Definición de rutas
├── storage/                # Logs, caché, archivos
├── tests/                  # Tests automatizados
├── docker/                 # Configuración de Docker
├── .env                    # Variables de entorno
├── .env.example           # Ejemplo de variables de entorno
├── docker-compose.yml     # Orquestación de servicios
└── README.md              # Documentación principal
```

#### Configuración de Git

```bash
# Inicializar repositorio
git init

# Configurar .gitignore
cat > .gitignore << EOF
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
docker-compose.override.yml
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
EOF

# Primer commit
git add .
git commit -m "Initial commit: Laravel project with Docker setup"
```

### 2. Flujo de Trabajo Diario

#### Iniciar el Día

```bash
# 1. Activar el entorno
docker-compose up -d

# 2. Verificar que todo funcione
curl http://localhost:8000

# 3. Ver logs si hay problemas
docker-compose logs -f
```

#### Durante el Desarrollo

```bash
# Ver logs en tiempo real
docker-compose logs -f app

# Ejecutar comandos de Artisan
docker exec monitorias_app php artisan [comando]

# Acceder a Tinker para debugging
docker exec -it monitorias_app php artisan tinker

# Ver rutas disponibles
docker exec monitorias_app php artisan route:list
```

#### Al Final del Día

```bash
# Guardar cambios
git add .
git commit -m "feat: descripción de los cambios"

# Opcional: hacer push
git push origin main

# Detener contenedores si no los necesitas
docker-compose down
```

### 3. Creación de Nuevas Funcionalidades

#### Crear un Nuevo Modelo

```bash
# Crear modelo con migración
docker exec monitorias_app php artisan make:model Monitoria -m

# Crear modelo con migración, factory y seeder
docker exec monitorias_app php artisan make:model Monitoria -mfs

# Crear modelo con todo (migración, factory, seeder, controlador)
docker exec monitorias_app php artisan make:model Monitoria -mfsr
```

#### Crear un Nuevo Controlador

```bash
# Controlador básico
docker exec monitorias_app php artisan make:controller MonitoriaController

# Controlador con métodos CRUD
docker exec monitorias_app php artisan make:controller MonitoriaController --resource

# Controlador API
docker exec monitorias_app php artisan make:controller API/MonitoriaController
```

#### Crear una Nueva Migración

```bash
# Migración para nueva tabla
docker exec monitorias_app php artisan make:migration create_monitorias_table

# Migración para agregar columna
docker exec monitorias_app php artisan make:migration add_status_to_monitorias_table

# Ejecutar migraciones
docker exec monitorias_app php artisan migrate

# Revertir última migración
docker exec monitorias_app php artisan migrate:rollback
```

#### Crear un Nuevo Request

```bash
# Request para validación
docker exec monitorias_app php artisan make:request StoreMonitoriaRequest
docker exec monitorias_app php artisan make:request UpdateMonitoriaRequest
```

#### Crear un Nuevo Middleware

```bash
# Middleware personalizado
docker exec monitorias_app php artisan make:middleware CheckMonitoriaAccess
```

### 4. Testing

#### Configuración de Tests

```bash
# Ejecutar todos los tests
docker exec monitorias_app php artisan test

# Ejecutar tests específicos
docker exec monitorias_app php artisan test --filter=MonitoriaTest

# Ejecutar tests con cobertura
docker exec monitorias_app php artisan test --coverage

# Ejecutar tests en paralelo
docker exec monitorias_app php artisan test --parallel
```

#### Crear Tests

```bash
# Test de feature
docker exec monitorias_app php artisan make:test MonitoriaTest

# Test de unidad
docker exec monitorias_app php artisan make:test MonitoriaTest --unit

# Test de API
docker exec monitorias_app php artisan make:test MonitoriaApiTest
```

#### Ejemplo de Test

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Monitoria;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MonitoriaTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_monitoria()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/monitorias', [
                'title' => 'Matemáticas',
                'description' => 'Clase de cálculo',
                'date' => '2025-08-01',
                'time' => '14:00'
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'monitoria' => [
                    'id',
                    'title',
                    'description',
                    'date',
                    'time'
                ]
            ]);
    }
}
```

## 📝 Convenciones de Código

### 1. Nomenclatura

#### Clases y Archivos

```php
// Controladores: PascalCase, sufijo Controller
class MonitoriaController extends Controller

// Modelos: PascalCase, singular
class Monitoria extends Model

// Requests: PascalCase, sufijo Request
class StoreMonitoriaRequest extends FormRequest

// Middleware: PascalCase, descriptivo
class CheckMonitoriaAccess
```

#### Variables y Métodos

```php
// Variables: camelCase
$monitoriaId = 1;
$userName = 'Juan';

// Métodos: camelCase
public function createMonitoria()
public function getUserById($id)

// Constantes: UPPER_SNAKE_CASE
const MAX_MONITORIAS_PER_USER = 10;
```

### 2. Estructura de Controladores

#### Controlador API Típico

```php
<?php

namespace App\Http\Controllers;

use App\Models\Monitoria;
use App\Http\Requests\StoreMonitoriaRequest;
use App\Http\Requests\UpdateMonitoriaRequest;
use Illuminate\Http\Request;

class MonitoriaController extends Controller
{
    public function index()
    {
        $monitorias = Monitoria::with('user')->paginate(10);

        return response()->json([
            'monitorias' => $monitorias,
            'status' => 200
        ]);
    }

    public function store(StoreMonitoriaRequest $request)
    {
        $monitoria = Monitoria::create($request->validated());

        return response()->json([
            'message' => 'Monitoria created successfully',
            'monitoria' => $monitoria,
            'status' => 201
        ], 201);
    }

    public function show(Monitoria $monitoria)
    {
        return response()->json([
            'monitoria' => $monitoria->load('user'),
            'status' => 200
        ]);
    }

    public function update(UpdateMonitoriaRequest $request, Monitoria $monitoria)
    {
        $monitoria->update($request->validated());

        return response()->json([
            'message' => 'Monitoria updated successfully',
            'monitoria' => $monitoria,
            'status' => 200
        ]);
    }

    public function destroy(Monitoria $monitoria)
    {
        $monitoria->delete();

        return response()->json([
            'message' => 'Monitoria deleted successfully',
            'status' => 200
        ]);
    }
}
```

### 3. Estructura de Requests

#### Request de Validación

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonitoriaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'date' => 'required|date|after:today',
            'time' => 'required|date_format:H:i',
            'subject' => 'required|string|max:100',
            'max_students' => 'nullable|integer|min:1|max:50'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'El título es requerido',
            'date.after' => 'La fecha debe ser posterior a hoy',
            'time.date_format' => 'El formato de hora debe ser HH:MM'
        ];
    }
}
```

### 4. Estructura de Modelos

#### Modelo con Relaciones

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'date',
        'time',
        'subject',
        'max_students',
        'user_id'
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime',
        'max_students' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }
}
```

## 🔧 Herramientas de Desarrollo

### 1. Debugging

#### Xdebug (Opcional)

```dockerfile
# Agregar a Dockerfile para desarrollo
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configuración en docker/php/local.ini
[xdebug]
xdebug.mode=debug
xdebug.start_with_request=yes
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
```

#### Logging

```php
// En controladores
Log::info('Monitoria created', ['id' => $monitoria->id]);
Log::error('Error creating monitoria', ['error' => $e->getMessage()]);

// Ver logs en tiempo real
docker exec monitorias_app tail -f /var/www/storage/logs/laravel.log
```

### 2. Optimización

#### Para Desarrollo

```bash
# Limpiar caché frecuentemente
docker exec monitorias_app php artisan config:clear
docker exec monitorias_app php artisan cache:clear

# Regenerar autoloader
docker exec monitorias_app composer dump-autoload
```

#### Para Producción

```bash
# Optimizar autoloader
docker exec monitorias_app composer install --optimize-autoloader --no-dev

# Cachear configuración
docker exec monitorias_app php artisan config:cache
docker exec monitorias_app php artisan route:cache
docker exec monitorias_app php artisan view:cache

# Optimizar aplicación
docker exec monitorias_app php artisan optimize
```

### 3. Monitoreo

#### Telescope (Debugging)

```bash
# Instalar Telescope
docker exec monitorias_app composer require laravel/telescope --dev

# Publicar configuración
docker exec monitorias_app php artisan telescope:install

# Ejecutar migraciones
docker exec monitorias_app php artisan migrate

# Acceder a Telescope
# http://localhost:8000/telescope
```

#### Health Checks

```bash
# Verificar estado de la aplicación
curl http://localhost:8000/api/test

# Verificar base de datos
docker exec monitorias_app php artisan tinker --execute="echo DB::connection()->getPdo() ? 'DB OK' : 'DB FAILED';"

# Verificar Redis
docker exec monitorias_app php artisan tinker --execute="echo Redis::ping() ? 'Redis OK' : 'Redis FAILED';"
```

## 📊 Base de Datos

### 1. Migraciones

#### Estructura de Migración

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monitorias', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->date('date');
            $table->time('time');
            $table->string('subject');
            $table->integer('max_students')->default(10);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->index(['date', 'subject']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('monitorias');
    }
};
```

### 2. Seeders

#### Seeder con Factory

```php
<?php

namespace Database\Seeders;

use App\Models\Monitoria;
use App\Models\User;
use Illuminate\Database\Seeder;

class MonitoriaSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();

        foreach ($users as $user) {
            Monitoria::factory()
                ->count(5)
                ->for($user)
                ->create();
        }
    }
}
```

### 3. Factories

#### Factory Completa

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonitoriaFactory extends Factory
{
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'time' => $this->faker->time('H:i'),
            'subject' => $this->faker->randomElement(['Matemáticas', 'Física', 'Química', 'Historia']),
            'max_students' => $this->faker->numberBetween(5, 20),
            'user_id' => User::factory()
        ];
    }
}
```

## 🔄 Git Workflow

### 1. Branching Strategy

```bash
# Rama principal
main

# Rama de desarrollo
develop

# Ramas de feature
feature/user-authentication
feature/monitoria-crud
feature/student-registration

# Ramas de hotfix
hotfix/critical-bug-fix
```

### 2. Commits

#### Convención de Commits

```bash
# Formato: tipo(alcance): descripción

# Ejemplos:
git commit -m "feat(auth): add user registration endpoint"
git commit -m "fix(api): resolve database connection issue"
git commit -m "docs(readme): update installation instructions"
git commit -m "test(monitoria): add unit tests for Monitoria model"
git commit -m "refactor(controllers): improve error handling"
```

#### Tipos de Commits

-   `feat`: Nueva funcionalidad
-   `fix`: Corrección de bug
-   `docs`: Documentación
-   `style`: Formato de código
-   `refactor`: Refactorización
-   `test`: Tests
-   `chore`: Tareas de mantenimiento

### 3. Pull Requests

#### Template de PR

```markdown
## Descripción

Breve descripción de los cambios realizados.

## Tipo de Cambio

-   [ ] Bug fix
-   [ ] Nueva funcionalidad
-   [ ] Breaking change
-   [ ] Documentación

## Cambios Realizados

-   Lista de cambios específicos

## Testing

-   [ ] Tests unitarios pasan
-   [ ] Tests de integración pasan
-   [ ] Manual testing realizado

## Checklist

-   [ ] Código sigue las convenciones del proyecto
-   [ ] Documentación actualizada
-   [ ] No hay warnings del linter
-   [ ] Commits siguen la convención
```

## 🚀 Deployment

### 1. Preparación para Producción

```bash
# Optimizar para producción
docker exec monitorias_app composer install --optimize-autoloader --no-dev
docker exec monitorias_app php artisan config:cache
docker exec monitorias_app php artisan route:cache
docker exec monitorias_app php artisan view:cache
docker exec monitorias_app php artisan optimize

# Configurar variables de entorno de producción
# Cambiar APP_ENV=production
# Cambiar APP_DEBUG=false
# Configurar credenciales de producción
```

### 2. Backup

```bash
# Backup de base de datos
docker exec monitorias_db mysqldump -u monitorias -p monitorias > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup de archivos
tar -czf files_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/app/
```

## 📚 Recursos Adicionales

-   [Laravel Best Practices](https://laravel.com/docs/best-practices)
-   [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
-   [Docker Best Practices](https://docs.docker.com/develop/dev-best-practices/)
-   [Git Flow](https://nvie.com/posts/a-successful-git-branching-model/)
-   [Conventional Commits](https://www.conventionalcommits.org/)
