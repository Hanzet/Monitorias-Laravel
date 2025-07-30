# Inyección de Dependencias en Laravel

## Introducción

La Inyección de Dependencias (DI) es un patrón de diseño que permite desacoplar las clases de sus dependencias. Laravel incluye un Container de Inyección de Dependencias que gestiona automáticamente la resolución y creación de objetos.

## 1. Container de Laravel

### ¿Qué es el Container?

El Container de Laravel es un gestor de dependencias que resuelve automáticamente las dependencias de las clases y las inyecta donde sea necesario.

```php
// El Container resuelve automáticamente las dependencias
class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService; // Inyección automática
    }
}
```

### Resolución Automática

```php
// Laravel puede resolver automáticamente dependencias simples
class PostService
{
    private $postRepository;
    private $emailService;

    public function __construct(
        PostRepository $postRepository,
        EmailService $emailService
    ) {
        $this->postRepository = $postRepository;
        $this->emailService = $emailService;
    }
}

// Uso en controlador
class PostController extends Controller
{
    public function store(PostRequest $request, PostService $postService)
    {
        // PostService se resuelve automáticamente
        $post = $postService->createPost($request->validated());

        return response()->json($post, 201);
    }
}
```

## 2. Binding de Interfaces

### Binding Básico

```php
// En AppServiceProvider
public function register()
{
    // Binding simple
    $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

    // Binding con closure
    $this->app->bind(UserService::class, function ($app) {
        return new UserService(
            $app->make(UserRepositoryInterface::class)
        );
    });
}

// Interfaces
interface UserRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}

// Implementación
class UserRepository implements UserRepositoryInterface
{
    public function all()
    {
        return User::all();
    }

    public function find($id)
    {
        return User::find($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        $user = User::find($id);
        $user->update($data);
        return $user;
    }

    public function delete($id)
    {
        return User::destroy($id);
    }
}

// Uso en servicio
class UserService
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data)
    {
        return $this->userRepository->create($data);
    }
}
```

### Binding Singleton

```php
// En AppServiceProvider
public function register()
{
    // Singleton - siempre la misma instancia
    $this->app->singleton(CacheService::class, function ($app) {
        return new CacheService($app->make('redis'));
    });

    // Singleton con interfaz
    $this->app->singleton(LoggerInterface::class, function ($app) {
        return new LoggerService();
    });
}

// Uso
class UserController extends Controller
{
    public function index(CacheService $cache)
    {
        // Siempre la misma instancia de CacheService
        return $cache->get('users');
    }
}
```

### Binding Instance

```php
// En AppServiceProvider
public function register()
{
    // Binding de instancia específica
    $this->app->instance('api.key', 'your-api-key-here');

    // Binding de instancia de clase
    $this->app->instance(ConfigService::class, new ConfigService([
        'api_url' => 'https://api.example.com',
        'timeout' => 30
    ]));
}

// Uso
class ApiService
{
    public function __construct(ConfigService $config)
    {
        $this->apiUrl = $config->get('api_url');
    }
}
```

## 3. Resolución Automática

### Resolución de Dependencias Anidadas

```php
class EmailService
{
    private $mailer;
    private $config;

    public function __construct(Mailer $mailer, ConfigService $config)
    {
        $this->mailer = $mailer;
        $this->config = $config;
    }
}

class NotificationService
{
    private $emailService;
    private $smsService;

    public function __construct(EmailService $emailService, SmsService $smsService)
    {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }
}

class UserController extends Controller
{
    public function store(UserRequest $request, NotificationService $notificationService)
    {
        // Laravel resuelve automáticamente toda la cadena de dependencias
        $user = User::create($request->validated());
        $notificationService->sendWelcomeNotification($user);

        return response()->json($user, 201);
    }
}
```

### Resolución con Parámetros

```php
// Resolución con parámetros específicos
class PaymentService
{
    private $gateway;
    private $config;

    public function __construct(PaymentGateway $gateway, array $config)
    {
        $this->gateway = $gateway;
        $this->config = $config;
    }
}

// En AppServiceProvider
public function register()
{
    $this->app->when(PaymentService::class)
              ->needs('$config')
              ->give([
                  'api_key' => config('payment.api_key'),
                  'secret' => config('payment.secret'),
                  'environment' => config('payment.environment')
              ]);
}
```

## 4. Singleton y Transient

### Binding Singleton

```php
// En AppServiceProvider
public function register()
{
    // Singleton - una sola instancia
    $this->app->singleton(DatabaseConnection::class, function ($app) {
        return new DatabaseConnection(config('database.default'));
    });

    // Singleton con interfaz
    $this->app->singleton(LoggerInterface::class, LoggerService::class);
}

// Uso
class UserService
{
    public function __construct(DatabaseConnection $db)
    {
        // Siempre la misma instancia de DatabaseConnection
        $this->db = $db;
    }
}
```

### Binding Transient (Por Defecto)

```php
// En AppServiceProvider
public function register()
{
    // Transient - nueva instancia cada vez
    $this->app->bind(UserService::class, function ($app) {
        return new UserService(
            $app->make(UserRepositoryInterface::class)
        );
    });
}

// Uso
class UserController extends Controller
{
    public function index(UserService $userService)
    {
        // Nueva instancia de UserService cada vez
        return $userService->getAllUsers();
    }
}
```

### Binding Scoped

```php
// En AppServiceProvider
public function register()
{
    // Scoped - una instancia por request
    $this->app->scoped(RequestLogger::class, function ($app) {
        return new RequestLogger($app->make('request'));
    });
}

// Uso
class UserController extends Controller
{
    public function store(UserRequest $request, RequestLogger $logger)
    {
        // Misma instancia durante todo el request
        $logger->log('Creating user');

        $user = User::create($request->validated());

        return response()->json($user, 201);
    }
}
```

## 5. Contextual Binding

### Binding Contextual

```php
// En AppServiceProvider
public function register()
{
    // Binding contextual
    $this->app->when(OrderProcessor::class)
              ->needs(PaymentGateway::class)
              ->give(StripeGateway::class);

    $this->app->when(SubscriptionProcessor::class)
              ->needs(PaymentGateway::class)
              ->give(PaypalGateway::class);
}

// Uso
class OrderProcessor
{
    public function __construct(PaymentGateway $gateway)
    {
        // Recibe StripeGateway
        $this->gateway = $gateway;
    }
}

class SubscriptionProcessor
{
    public function __construct(PaymentGateway $gateway)
    {
        // Recibe PaypalGateway
        $this->gateway = $gateway;
    }
}
```

### Binding con Condiciones

```php
// En AppServiceProvider
public function register()
{
    $this->app->when(NotificationService::class)
              ->needs('$config')
              ->give(function () {
                  return [
                      'email_enabled' => config('notifications.email.enabled'),
                      'sms_enabled' => config('notifications.sms.enabled'),
                      'push_enabled' => config('notifications.push.enabled')
                  ];
              });
}

class NotificationService
{
    public function __construct(array $config)
    {
        $this->emailEnabled = $config['email_enabled'];
        $this->smsEnabled = $config['sms_enabled'];
        $this->pushEnabled = $config['push_enabled'];
    }
}
```

## 6. Resolución Manual

### Resolución con app()

```php
// Resolución manual
$userService = app(UserService::class);

// Resolución con make()
$userService = app()->make(UserService::class);

// Resolución con parámetros
$paymentService = app(PaymentService::class, ['config' => $config]);

// Resolución de interfaz
$repository = app(UserRepositoryInterface::class);
```

### Resolución en Métodos

```php
class UserController extends Controller
{
    public function store(UserRequest $request)
    {
        // Resolución manual en método
        $userService = app(UserService::class);
        $user = $userService->createUser($request->validated());

        return response()->json($user, 201);
    }

    public function update(UserRequest $request, $id)
    {
        // Resolución con parámetros
        $userService = app(UserService::class);
        $user = $userService->updateUser($id, $request->validated());

        return response()->json($user);
    }
}
```

## 7. Service Providers

### Crear Service Provider

```bash
php artisan make:provider UserServiceProvider
```

### Registrar Bindings

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\UserService;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Services\EmailService;
use App\Services\SmsService;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Binding de interfaz
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Binding de servicio
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepositoryInterface::class),
                $app->make(EmailService::class)
            );
        });

        // Singleton para configuración
        $this->app->singleton('user.config', function () {
            return [
                'max_users' => config('user.max_users', 1000),
                'auto_verify' => config('user.auto_verify', false),
                'welcome_email' => config('user.welcome_email', true)
            ];
        });
    }

    public function boot()
    {
        // Configuración después de que todos los servicios estén registrados
        $config = $this->app->make('user.config');

        // Configurar servicios basados en configuración
        if ($config['auto_verify']) {
            // Configuración adicional
        }
    }
}
```

### Registrar Service Provider

```php
// config/app.php
'providers' => [
    // ...
    App\Providers\UserServiceProvider::class,
],
```

## 8. Ejemplos Prácticos

### Sistema de Notificaciones

```php
// Interfaces
interface NotificationInterface
{
    public function send($user, $message);
}

interface EmailInterface
{
    public function send($to, $subject, $body);
}

interface SmsInterface
{
    public function send($to, $message);
}

// Implementaciones
class EmailService implements EmailInterface
{
    public function send($to, $subject, $body)
    {
        // Lógica para enviar email
        Mail::to($to)->send(new GenericMail($subject, $body));
    }
}

class SmsService implements SmsInterface
{
    public function send($to, $message)
    {
        // Lógica para enviar SMS
        // Integración con servicio SMS
    }
}

class NotificationService implements NotificationInterface
{
    private $emailService;
    private $smsService;

    public function __construct(EmailInterface $emailService, SmsInterface $smsService)
    {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }

    public function send($user, $message)
    {
        // Enviar email
        $this->emailService->send($user->email, 'Notificación', $message);

        // Enviar SMS si tiene teléfono
        if ($user->phone) {
            $this->smsService->send($user->phone, $message);
        }
    }
}

// Service Provider
class NotificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(EmailInterface::class, EmailService::class);
        $this->app->bind(SmsInterface::class, SmsService::class);
        $this->app->bind(NotificationInterface::class, NotificationService::class);
    }
}

// Uso en controlador
class UserController extends Controller
{
    public function store(UserRequest $request, NotificationInterface $notification)
    {
        $user = User::create($request->validated());

        $notification->send($user, 'Bienvenido a nuestra aplicación');

        return response()->json($user, 201);
    }
}
```

### Sistema de Pagos

```php
// Interfaces
interface PaymentGatewayInterface
{
    public function charge($amount, $token);
    public function refund($transactionId);
}

interface PaymentProcessorInterface
{
    public function processPayment($order, $paymentData);
}

// Implementaciones
class StripeGateway implements PaymentGatewayInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function charge($amount, $token)
    {
        // Lógica de Stripe
        return ['success' => true, 'transaction_id' => 'stripe_123'];
    }

    public function refund($transactionId)
    {
        // Lógica de refund en Stripe
        return ['success' => true];
    }
}

class PaypalGateway implements PaymentGatewayInterface
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function charge($amount, $token)
    {
        // Lógica de PayPal
        return ['success' => true, 'transaction_id' => 'paypal_456'];
    }

    public function refund($transactionId)
    {
        // Lógica de refund en PayPal
        return ['success' => true];
    }
}

class PaymentProcessor implements PaymentProcessorInterface
{
    private $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function processPayment($order, $paymentData)
    {
        $result = $this->gateway->charge($order->total, $paymentData['token']);

        if ($result['success']) {
            $order->update(['status' => 'paid', 'transaction_id' => $result['transaction_id']]);
        }

        return $result;
    }
}

// Service Provider
class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Binding contextual
        $this->app->when(PaymentProcessor::class)
                  ->needs(PaymentGatewayInterface::class)
                  ->give(function ($app) {
                      $gateway = request()->input('gateway', 'stripe');

                      if ($gateway === 'paypal') {
                          return new PaypalGateway(config('payment.paypal'));
                      }

                      return new StripeGateway(config('payment.stripe'));
                  });
    }
}

// Uso en controlador
class OrderController extends Controller
{
    public function processPayment(Request $request, PaymentProcessorInterface $paymentProcessor)
    {
        $order = Order::findOrFail($request->order_id);

        $result = $paymentProcessor->processPayment($order, $request->all());

        return response()->json($result);
    }
}
```

## 9. Mejores Prácticas

### 1. Usar Interfaces

```php
// ✅ Bien - Usar interfaces
class UserService
{
    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
}

// ❌ Mal - Depender de implementaciones concretas
class UserService
{
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }
}
```

### 2. Registrar en Service Providers

```php
// ✅ Bien - Registrar en Service Provider
class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}

// ❌ Mal - Registrar en controlador
class UserController extends Controller
{
    public function __construct()
    {
        app()->bind(UserRepositoryInterface::class, UserRepository::class);
    }
}
```

### 3. Usar Binding Contextual

```php
// ✅ Bien - Binding contextual
$this->app->when(OrderProcessor::class)
          ->needs(PaymentGateway::class)
          ->give(StripeGateway::class);

// ❌ Mal - Lógica condicional en constructor
class OrderProcessor
{
    public function __construct()
    {
        if (config('payment.gateway') === 'stripe') {
            $this->gateway = new StripeGateway();
        } else {
            $this->gateway = new PaypalGateway();
        }
    }
}
```

## Conclusión

La Inyección de Dependencias en Laravel proporciona una forma elegante y flexible de gestionar las dependencias entre clases. Al utilizar el Container de Laravel, puedes crear aplicaciones más mantenibles, testables y desacopladas.
