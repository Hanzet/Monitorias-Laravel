# Clases en Laravel

## Introducción

Laravel está construido completamente sobre clases PHP. Cada componente del framework es una clase que sigue principios de POO. En este documento aprenderemos cómo estructurar y organizar clases en Laravel.

## 1. Estructura de una Clase

### Estructura Básica

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    // Propiedades de la clase
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    // Métodos de la clase
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
```

### Elementos de una Clase

1. **Namespace**: Organiza las clases en espacios de nombres
2. **Imports**: Importa otras clases necesarias
3. **Traits**: Reutiliza funcionalidad entre clases
4. **Propiedades**: Almacenan datos del objeto
5. **Métodos**: Definen el comportamiento
6. **Constantes**: Valores inmutables

## 2. Namespaces

### ¿Qué son los Namespaces?

Los namespaces organizan las clases y evitan conflictos de nombres. Laravel usa una estructura de namespaces específica:

```php
// Estructura de namespaces en Laravel
namespace App\Models;           // Modelos
namespace App\Http\Controllers; // Controladores
namespace App\Services;         // Servicios
namespace App\Repositories;     // Repositorios
namespace App\Traits;           // Traits
namespace App\Interfaces;       // Interfaces
namespace App\Exceptions;       // Excepciones
```

### Ejemplo de Namespace

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use App\Events\UserRegistered;

class UserService
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data): User
    {
        // Lógica del servicio
    }
}
```

### Importación de Clases

```php
<?php

namespace App\Http\Controllers;

// Importaciones específicas
use App\Models\User;
use App\Models\Post;
use App\Services\UserService;

// Importaciones con alias
use App\Services\UserService as UserManager;

// Importaciones de grupos
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\{Hash, Mail, Auth};

class UserController extends Controller
{
    // Uso de las clases importadas
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }
}
```

## 3. Autoloading

### PSR-4 Autoloading

Laravel usa PSR-4 para el autoloading automático de clases:

```json
// composer.json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    }
}
```

### Estructura de Directorios

```
app/
├── Console/
├── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Providers/
├── Services/
├── Repositories/
├── Traits/
└── Interfaces/
```

### Ejemplo de Autoloading

```php
// app/Services/UserService.php
namespace App\Services;

class UserService
{
    // Clase automáticamente cargada
}

// app/Http/Controllers/UserController.php
namespace App\Http\Controllers;

use App\Services\UserService; // Autoloading automático

class UserController extends Controller
{
    public function __construct(UserService $userService)
    {
        // Inyección automática
    }
}
```

## 4. Clases de Servicio

### ¿Qué son las Clases de Servicio?

Las clases de servicio encapsulan la lógica de negocio y operaciones complejas. No están atadas a HTTP requests ni a la base de datos directamente.

### Estructura de una Clase de Servicio

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Post;
use App\Repositories\UserRepositoryInterface;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

class UserService
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Crear un nuevo usuario
     */
    public function createUser(array $data): User
    {
        // Validación
        $this->validateUserData($data);

        // Preparar datos
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();

        // Crear usuario
        $user = $this->userRepository->create($data);

        // Enviar email de bienvenida
        $this->sendWelcomeEmail($user);

        // Disparar evento
        event(new UserRegistered($user));

        return $user;
    }

    /**
     * Actualizar usuario existente
     */
    public function updateUser(int $userId, array $data): User
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }

        // Validar email único si cambió
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $this->validateEmailUnique($data['email']);
        }

        // Actualizar contraseña si se proporcionó
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($userId, $data);
    }

    /**
     * Eliminar usuario
     */
    public function deleteUser(int $userId): bool
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }

        // Verificar si tiene posts
        if ($user->posts()->count() > 0) {
            throw new \Exception('No se puede eliminar usuario con posts');
        }

        return $this->userRepository->delete($userId);
    }

    /**
     * Buscar usuarios con filtros
     */
    public function searchUsers(array $filters): \Illuminate\Database\Eloquent\Collection
    {
        return $this->userRepository->search($filters);
    }

    /**
     * Validar datos del usuario
     */
    private function validateUserData(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('El nombre es requerido');
        }

        if (empty($data['email'])) {
            throw new \InvalidArgumentException('El email es requerido');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido');
        }

        if (empty($data['password'])) {
            throw new \InvalidArgumentException('La contraseña es requerida');
        }

        if (strlen($data['password']) < 8) {
            throw new \InvalidArgumentException('La contraseña debe tener al menos 8 caracteres');
        }
    }

    /**
     * Validar email único
     */
    private function validateEmailUnique(string $email): void
    {
        if ($this->userRepository->findByEmail($email)) {
            throw new \InvalidArgumentException('El email ya está registrado');
        }
    }

    /**
     * Enviar email de bienvenida
     */
    private function sendWelcomeEmail(User $user): void
    {
        Mail::to($user->email)->send(new WelcomeEmail($user));
    }
}
```

### Uso de Clases de Servicio

```php
// En un Controller
class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(Request $request)
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $user
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}
```

## 5. Clases Utilitarias

### ¿Qué son las Clases Utilitarias?

Las clases utilitarias contienen métodos estáticos que proporcionan funcionalidad común y reutilizable.

### Ejemplo de Clase Utilitaria

```php
<?php

namespace App\Utils;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class FileUtils
{
    /**
     * Generar nombre único para archivo
     */
    public static function generateUniqueFileName(string $originalName, string $extension = null): string
    {
        $extension = $extension ?: pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);

        return Str::slug($name) . '_' . time() . '.' . $extension;
    }

    /**
     * Guardar archivo en storage
     */
    public static function storeFile($file, string $path = 'uploads'): string
    {
        $fileName = self::generateUniqueFileName($file->getClientOriginalName());
        $filePath = $file->storeAs($path, $fileName, 'public');

        return $filePath;
    }

    /**
     * Eliminar archivo del storage
     */
    public static function deleteFile(string $filePath): bool
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }

        return false;
    }

    /**
     * Obtener URL pública del archivo
     */
    public static function getFileUrl(string $filePath): string
    {
        return Storage::disk('public')->url($filePath);
    }

    /**
     * Validar tipo de archivo
     */
    public static function validateFileType($file, array $allowedTypes): bool
    {
        $mimeType = $file->getMimeType();
        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Validar tamaño de archivo
     */
    public static function validateFileSize($file, int $maxSizeInMB): bool
    {
        $maxSizeInBytes = $maxSizeInMB * 1024 * 1024;
        return $file->getSize() <= $maxSizeInBytes;
    }
}

class StringUtils
{
    /**
     * Limpiar string de caracteres especiales
     */
    public static function cleanString(string $string): string
    {
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $string);
    }

    /**
     * Generar slug único
     */
    public static function generateSlug(string $title, string $model = null, int $id = null): string
    {
        $slug = Str::slug($title);

        if ($model && $id) {
            $count = $model::where('slug', 'LIKE', $slug . '%')->count();
            if ($count > 0) {
                $slug .= '-' . ($count + 1);
            }
        }

        return $slug;
    }

    /**
     * Ocultar parte de un email
     */
    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $username = $parts[0];
        $domain = $parts[1];

        $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);

        return $maskedUsername . '@' . $domain;
    }

    /**
     * Formatear número de teléfono
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
        }

        return $phone;
    }
}

class DateUtils
{
    /**
     * Formatear fecha para mostrar
     */
    public static function formatDate($date, string $format = 'd/m/Y'): string
    {
        if (!$date) return '';

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        return $date->format($format);
    }

    /**
     * Calcular edad
     */
    public static function calculateAge($birthDate): int
    {
        if (!$birthDate) return 0;

        if (is_string($birthDate)) {
            $birthDate = new \DateTime($birthDate);
        }

        $now = new \DateTime();
        $interval = $now->diff($birthDate);

        return $interval->y;
    }

    /**
     * Verificar si es fin de semana
     */
    public static function isWeekend($date): bool
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        return $date->format('N') >= 6;
    }

    /**
     * Obtener días hábiles entre dos fechas
     */
    public static function getWorkingDays($startDate, $endDate): int
    {
        if (is_string($startDate)) {
            $startDate = new \DateTime($startDate);
        }

        if (is_string($endDate)) {
            $endDate = new \DateTime($endDate);
        }

        $workingDays = 0;
        $currentDate = clone $startDate;

        while ($currentDate <= $endDate) {
            if (!self::isWeekend($currentDate)) {
                $workingDays++;
            }
            $currentDate->add(new \DateInterval('P1D'));
        }

        return $workingDays;
    }
}
```

### Uso de Clases Utilitarias

```php
// En un Controller
class FileController extends Controller
{
    public function upload(Request $request)
    {
        $file = $request->file('document');

        // Validar archivo
        if (!FileUtils::validateFileType($file, ['application/pdf', 'image/jpeg', 'image/png'])) {
            return response()->json(['error' => 'Tipo de archivo no permitido'], 400);
        }

        if (!FileUtils::validateFileSize($file, 5)) { // 5MB
            return response()->json(['error' => 'Archivo demasiado grande'], 400);
        }

        // Guardar archivo
        $filePath = FileUtils::storeFile($file, 'documents');

        return response()->json([
            'success' => true,
            'file_path' => $filePath,
            'file_url' => FileUtils::getFileUrl($filePath)
        ]);
    }
}

// En un Model
class Post extends Model
{
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = StringUtils::generateSlug($value, Post::class, $this->id);
    }
}
```

## 6. Organización de Clases

### Estructura Recomendada

```
app/
├── Services/           # Lógica de negocio
│   ├── UserService.php
│   ├── PostService.php
│   └── EmailService.php
├── Repositories/       # Acceso a datos
│   ├── UserRepository.php
│   ├── PostRepository.php
│   └── Interfaces/
│       ├── UserRepositoryInterface.php
│       └── PostRepositoryInterface.php
├── Utils/             # Clases utilitarias
│   ├── FileUtils.php
│   ├── StringUtils.php
│   └── DateUtils.php
├── Traits/            # Funcionalidad reutilizable
│   ├── HasSlug.php
│   ├── Searchable.php
│   └── Filterable.php
├── Interfaces/        # Contratos
│   ├── NotificationInterface.php
│   └── PaymentInterface.php
└── Exceptions/        # Excepciones personalizadas
    ├── UserNotFoundException.php
    └── InvalidDataException.php
```

### Convenciones de Nomenclatura

```php
// Clases de Servicio
class UserService
class PostService
class EmailService

// Clases de Repositorio
class UserRepository
class PostRepository
class CommentRepository

// Clases Utilitarias
class FileUtils
class StringUtils
class DateUtils

// Interfaces
interface UserRepositoryInterface
interface NotificationInterface
interface PaymentInterface

// Traits
trait HasSlug
trait Searchable
trait Filterable

// Excepciones
class UserNotFoundException
class InvalidDataException
class PaymentFailedException
```

## 7. Mejores Prácticas

### 1. Responsabilidad Única

```php
// ❌ Mal - Múltiples responsabilidades
class UserManager
{
    public function createUser() { /* ... */ }
    public function sendEmail() { /* ... */ }
    public function processPayment() { /* ... */ }
    public function generateReport() { /* ... */ }
}

// ✅ Bien - Responsabilidad única
class UserService
{
    public function createUser() { /* ... */ }
    public function updateUser() { /* ... */ }
    public function deleteUser() { /* ... */ }
}

class EmailService
{
    public function sendWelcomeEmail() { /* ... */ }
    public function sendPasswordReset() { /* ... */ }
}
```

### 2. Inyección de Dependencias

```php
// ❌ Mal - Dependencias hardcodeadas
class UserService
{
    public function createUser($data)
    {
        $user = User::create($data);
        Mail::to($user->email)->send(new WelcomeEmail());
    }
}

// ✅ Bien - Inyección de dependencias
class UserService
{
    private $userRepository;
    private $emailService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        EmailService $emailService
    ) {
        $this->userRepository = $userRepository;
        $this->emailService = $emailService;
    }

    public function createUser($data)
    {
        $user = $this->userRepository->create($data);
        $this->emailService->sendWelcomeEmail($user);
        return $user;
    }
}
```

### 3. Manejo de Errores

```php
class UserService
{
    public function createUser(array $data): User
    {
        try {
            // Validar datos
            $this->validateUserData($data);

            // Crear usuario
            $user = $this->userRepository->create($data);

            // Enviar email
            $this->emailService->sendWelcomeEmail($user);

            return $user;

        } catch (ValidationException $e) {
            Log::error('Error de validación al crear usuario', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;

        } catch (Exception $e) {
            Log::error('Error inesperado al crear usuario', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw new UserCreationException('No se pudo crear el usuario');
        }
    }
}
```

### 4. Documentación

```php
/**
 * Servicio para gestionar operaciones de usuarios
 *
 * @package App\Services
 * @author Tu Nombre
 * @version 1.0
 */
class UserService
{
    /**
     * Crear un nuevo usuario
     *
     * @param array $data Datos del usuario
     * @return User Usuario creado
     * @throws ValidationException Si los datos son inválidos
     * @throws UserCreationException Si no se puede crear el usuario
     */
    public function createUser(array $data): User
    {
        // Implementación
    }

    /**
     * Validar datos del usuario
     *
     * @param array $data Datos a validar
     * @throws ValidationException Si los datos son inválidos
     */
    private function validateUserData(array $data): void
    {
        // Validación
    }
}
```

## Conclusión

Las clases en Laravel son la base de toda la arquitectura del framework. Al organizar correctamente las clases siguiendo principios de POO y las mejores prácticas de Laravel, puedes crear aplicaciones más mantenibles, escalables y fáciles de probar.
