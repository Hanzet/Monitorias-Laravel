# Interfaces en Laravel

## ¿Qué son las Interfaces?

Las interfaces son contratos que definen qué métodos debe implementar una clase, sin especificar cómo se implementan.

## Características Principales

-   **Contrato**: Define qué métodos debe tener una clase
-   **Sin implementación**: Solo declara métodos, no los implementa
-   **Múltiples implementaciones**: Una clase puede implementar múltiples interfaces
-   **Polimorfismo**: Permite usar diferentes implementaciones de forma intercambiable

## Sintaxis Básica

```php
interface PaymentInterface
{
    public function process($amount);
    public function refund($transactionId);
}

class CreditCardPayment implements PaymentInterface
{
    public function process($amount)
    {
        // Implementación específica para tarjeta de crédito
        return "Procesando pago de {$amount} con tarjeta de crédito";
    }

    public function refund($transactionId)
    {
        return "Reembolso de transacción {$transactionId}";
    }
}
```

## Interfaces Comunes en Laravel

### 1. Interfaces de Autenticación

```php
interface Authenticatable
{
    public function getAuthIdentifierName();
    public function getAuthIdentifier();
    public function getAuthPassword();
    public function getRememberToken();
    public function setRememberToken($value);
    public function getRememberTokenName();
}

// En el modelo User
class User extends Authenticatable implements Authenticatable
{
    // Laravel ya implementa estos métodos automáticamente
}
```

### 2. Interfaces de Notificaciones

```php
interface Notifiable
{
    public function routeNotificationFor($driver, $notification = null);
    public function notifications();
    public function readNotifications();
    public function unreadNotifications();
}

// En el modelo User
class User extends Authenticatable implements Notifiable
{
    use Notifiable; // Trait que implementa la interfaz
}
```

### 3. Interfaces de Cache

```php
interface CacheInterface
{
    public function get($key, $default = null);
    public function put($key, $value, $ttl = null);
    public function forget($key);
    public function has($key);
}

class RedisCache implements CacheInterface
{
    public function get($key, $default = null)
    {
        $value = Redis::get($key);
        return $value ?: $default;
    }

    public function put($key, $value, $ttl = null)
    {
        return Redis::setex($key, $ttl ?? 3600, $value);
    }

    public function forget($key)
    {
        return Redis::del($key);
    }

    public function has($key)
    {
        return Redis::exists($key);
    }
}
```

## Creando Interfaces Personalizadas

### 1. Interfaz de Servicios

```php
interface UserServiceInterface
{
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
}

class UserService implements UserServiceInterface
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
```

### 2. Interfaz de Repositorios

```php
interface PostRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Post;
    public function create(array $data): Post;
    public function update(Post $post, array $data): Post;
    public function delete(Post $post): bool;
    public function findByUser(int $userId): Collection;
    public function search(string $query): Collection;
}

class PostRepository implements PostRepositoryInterface
{
    public function all(): Collection
    {
        return Post::with('user')->get();
    }

    public function find(int $id): ?Post
    {
        return Post::with('user')->find($id);
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        return $post;
    }

    public function delete(Post $post): bool
    {
        return $post->delete();
    }

    public function findByUser(int $userId): Collection
    {
        return Post::where('user_id', $userId)->get();
    }

    public function search(string $query): Collection
    {
        return Post::where('title', 'like', "%{$query}%")
                   ->orWhere('content', 'like', "%{$query}%")
                   ->get();
    }
}
```

## Registrando Interfaces en el Container

### 1. Binding Básico

```php
// En AppServiceProvider
public function register()
{
    $this->app->bind(UserServiceInterface::class, UserService::class);
    $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
}
```

### 2. Binding Singleton

```php
public function register()
{
    $this->app->singleton(CacheInterface::class, RedisCache::class);
}
```

### 3. Binding Condicional

```php
public function register()
{
    $this->app->bind(PaymentInterface::class, function ($app) {
        if (config('app.env') === 'production') {
            return new StripePayment();
        }

        return new MockPayment();
    });
}
```

## Usando Interfaces en Controladores

```php
class UserController extends Controller
{
    private $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    public function store(Request $request)
    {
        $user = $this->userService->create($request->validated());
        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $user = $this->userService->update($user, $request->validated());
        return response()->json($user);
    }
}
```

## Interfaces para Testing

```php
interface EmailServiceInterface
{
    public function send(string $to, string $subject, string $body): bool;
}

class EmailService implements EmailServiceInterface
{
    public function send(string $to, string $subject, string $body): bool
    {
        // Implementación real
        return Mail::to($to)->send(new GenericMail($subject, $body));
    }
}

class MockEmailService implements EmailServiceInterface
{
    public function send(string $to, string $subject, string $body): bool
    {
        // Simulación para testing
        Log::info("Email enviado a: {$to}, Asunto: {$subject}");
        return true;
    }
}

// En el test
public function test_user_registration_sends_email()
{
    $this->app->bind(EmailServiceInterface::class, MockEmailService::class);

    $response = $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password'
    ]);

    $response->assertStatus(201);
}
```

## Interfaces para Eventos

```php
interface EventDispatcherInterface
{
    public function dispatch($event);
    public function listen($event, $listener);
    public function forget($event);
}

class CustomEventDispatcher implements EventDispatcherInterface
{
    private $listeners = [];

    public function dispatch($event)
    {
        if (isset($this->listeners[get_class($event)])) {
            foreach ($this->listeners[get_class($event)] as $listener) {
                $listener($event);
            }
        }
    }

    public function listen($event, $listener)
    {
        $this->listeners[$event][] = $listener;
    }

    public function forget($event)
    {
        unset($this->listeners[$event]);
    }
}
```

## Interfaces para Validación

```php
interface ValidatorInterface
{
    public function validate(array $data, array $rules): array;
    public function fails(): bool;
    public function errors(): array;
}

class CustomValidator implements ValidatorInterface
{
    private $errors = [];
    private $failed = false;

    public function validate(array $data, array $rules): array
    {
        // Implementación de validación personalizada
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field])) {
                $this->errors[$field][] = "El campo {$field} es requerido";
                $this->failed = true;
            }
        }

        return $data;
    }

    public function fails(): bool
    {
        return $this->failed;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
```

## Mejores Prácticas

### 1. Nombres Descriptivos

```php
// ✅ Bueno
interface UserRepositoryInterface
interface PaymentProcessorInterface
interface EmailNotifierInterface

// ❌ Malo
interface UserInterface
interface PaymentInterface
interface EmailInterface
```

### 2. Métodos Específicos

```php
// ✅ Bueno
interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByUsername(string $username): ?User;
    public function findActiveUsers(): Collection;
}

// ❌ Malo
interface UserRepositoryInterface
{
    public function find($criteria); // Muy genérico
}
```

### 3. Documentación

```php
interface UserServiceInterface
{
    /**
     * Crear un nuevo usuario
     *
     * @param array $data Datos del usuario
     * @return User Usuario creado
     * @throws ValidationException Si los datos son inválidos
     */
    public function create(array $data): User;

    /**
     * Actualizar un usuario existente
     *
     * @param User $user Usuario a actualizar
     * @param array $data Datos a actualizar
     * @return User Usuario actualizado
     */
    public function update(User $user, array $data): User;
}
```

### 4. Interfaces Pequeñas

```php
// ✅ Bueno - Interfaces específicas
interface UserReaderInterface
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
}

interface UserWriterInterface
{
    public function create(array $data): User;
    public function update(User $user, array $data): User;
    public function delete(User $user): bool;
}

// Una clase puede implementar múltiples interfaces
class UserRepository implements UserReaderInterface, UserWriterInterface
{
    // Implementación...
}
```

## Casos de Uso Comunes

### 1. Cambio de Implementación

```php
// Fácil cambio entre implementaciones
$this->app->bind(PaymentInterface::class, StripePayment::class);
// Cambiar a:
$this->app->bind(PaymentInterface::class, PayPalPayment::class);
```

### 2. Testing

```php
// En tests, usar implementaciones mock
$this->app->bind(EmailServiceInterface::class, MockEmailService::class);
```

### 3. Configuración por Entorno

```php
// Diferentes implementaciones según el entorno
if (app()->environment('local')) {
    $this->app->bind(LogInterface::class, FileLogger::class);
} else {
    $this->app->bind(LogInterface::class, CloudLogger::class);
}
```

Las interfaces son fundamentales en Laravel para crear código desacoplado, testeable y mantenible. Permiten cambiar implementaciones sin afectar el código que las usa.
