# Polimorfismo en Laravel

## ¿Qué es el Polimorfismo?

El polimorfismo es la capacidad de objetos de diferentes clases de responder al mismo mensaje de manera diferente, permitiendo usar una interfaz común para diferentes implementaciones.

## Características Principales

- **Interfaz Única**: Múltiples clases pueden implementar la misma interfaz
- **Comportamiento Diferente**: Cada clase responde de manera específica
- **Flexibilidad**: Se puede cambiar implementaciones sin modificar el código cliente
- **Extensibilidad**: Fácil agregar nuevas implementaciones

## Tipos de Polimorfismo

### 1. Polimorfismo de Inclusión (Herencia)

```php
abstract class Animal
{
    abstract public function makeSound();
    abstract public function move();
}

class Dog extends Animal
{
    public function makeSound()
    {
        return "Guau guau!";
    }

    public function move()
    {
        return "Corriendo en cuatro patas";
    }
}

class Cat extends Animal
{
    public function makeSound()
    {
        return "Miau miau!";
    }

    public function move()
    {
        return "Caminando sigilosamente";
    }
}

class Bird extends Animal
{
    public function makeSound()
    {
        return "Pío pío!";
    }

    public function move()
    {
        return "Volando";
    }
}

// Función polimórfica
function animalBehavior(Animal $animal)
{
    echo "Sonido: " . $animal->makeSound() . "\n";
    echo "Movimiento: " . $animal->move() . "\n";
}

// Uso
$dog = new Dog();
$cat = new Cat();
$bird = new Bird();

animalBehavior($dog);   // Usa métodos de Dog
animalBehavior($cat);   // Usa métodos de Cat
animalBehavior($bird);  // Usa métodos de Bird
```

### 2. Polimorfismo de Interfaces

```php
interface PaymentMethod
{
    public function process($amount);
    public function refund($transactionId);
}

class CreditCardPayment implements PaymentMethod
{
    public function process($amount)
    {
        return "Procesando pago de {$amount} con tarjeta de crédito";
    }

    public function refund($transactionId)
    {
        return "Reembolso de transacción {$transactionId} por tarjeta";
    }
}

class PayPalPayment implements PaymentMethod
{
    public function process($amount)
    {
        return "Procesando pago de {$amount} con PayPal";
    }

    public function refund($transactionId)
    {
        return "Reembolso de transacción {$transactionId} por PayPal";
    }
}

class BankTransferPayment implements PaymentMethod
{
    public function process($amount)
    {
        return "Procesando transferencia bancaria de {$amount}";
    }

    public function refund($transactionId)
    {
        return "Reembolso de transferencia {$transactionId}";
    }
}

class PaymentProcessor
{
    public function processPayment(PaymentMethod $payment, $amount)
    {
        return $payment->process($amount);
    }

    public function refundPayment(PaymentMethod $payment, $transactionId)
    {
        return $payment->refund($transactionId);
    }
}

// Uso
$processor = new PaymentProcessor();

$creditCard = new CreditCardPayment();
$paypal = new PayPalPayment();
$bankTransfer = new BankTransferPayment();

echo $processor->processPayment($creditCard, 100);    // Tarjeta
echo $processor->processPayment($paypal, 100);        // PayPal
echo $processor->processPayment($bankTransfer, 100);  // Transferencia
```

## Polimorfismo en Laravel

### 1. Polimorfismo en Modelos

```php
interface Searchable
{
    public function search($query);
    public function getSearchableFields();
}

class User extends Model implements Searchable
{
    protected $fillable = ['name', 'email', 'bio'];

    public function search($query)
    {
        return $this->where('name', 'like', "%{$query}%")
                   ->orWhere('email', 'like', "%{$query}%")
                   ->orWhere('bio', 'like', "%{$query}%")
                   ->get();
    }

    public function getSearchableFields()
    {
        return ['name', 'email', 'bio'];
    }
}

class Post extends Model implements Searchable
{
    protected $fillable = ['title', 'content', 'user_id'];

    public function search($query)
    {
        return $this->where('title', 'like', "%{$query}%")
                   ->orWhere('content', 'like', "%{$query}%")
                   ->with('user')
                   ->get();
    }

    public function getSearchableFields()
    {
        return ['title', 'content'];
    }
}

class Comment extends Model implements Searchable
{
    protected $fillable = ['content', 'user_id', 'post_id'];

    public function search($query)
    {
        return $this->where('content', 'like', "%{$query}%")
                   ->with(['user', 'post'])
                   ->get();
    }

    public function getSearchableFields()
    {
        return ['content'];
    }
}

class SearchService
{
    public function searchAll(Searchable $model, $query)
    {
        return $model->search($query);
    }

    public function getSearchableFields(Searchable $model)
    {
        return $model->getSearchableFields();
    }
}

// Uso
$searchService = new SearchService();

$user = new User();
$post = new Post();
$comment = new Comment();

$users = $searchService->searchAll($user, 'john');
$posts = $searchService->searchAll($post, 'laravel');
$comments = $searchService->searchAll($comment, 'excelente');
```

### 2. Polimorfismo en Controladores

```php
interface ApiController
{
    public function index();
    public function store(Request $request);
    public function show($id);
    public function update(Request $request, $id);
    public function destroy($id);
}

class UserController extends Controller implements ApiController
{
    public function index()
    {
        $users = User::paginate(15);
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $user = User::create($request->validated());
        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->validated());
        return response()->json($user);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }
}

class PostController extends Controller implements ApiController
{
    public function index()
    {
        $posts = Post::with('user')->paginate(15);
        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $post = Post::create($request->validated());
        return response()->json($post->load('user'), 201);
    }

    public function show($id)
    {
        $post = Post::with('user', 'comments')->findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->update($request->validated());
        return response()->json($post->load('user'));
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return response()->json(null, 204);
    }
}

class ApiResponseHandler
{
    public function handleIndex(ApiController $controller)
    {
        return $controller->index();
    }

    public function handleStore(ApiController $controller, Request $request)
    {
        return $controller->store($request);
    }

    public function handleShow(ApiController $controller, $id)
    {
        return $controller->show($id);
    }
}
```

### 3. Polimorfismo en Servicios

```php
interface NotificationService
{
    public function send($to, $message);
    public function sendBulk($recipients, $message);
}

class EmailNotificationService implements NotificationService
{
    public function send($to, $message)
    {
        return Mail::to($to)->send(new GenericMail($message));
    }

    public function sendBulk($recipients, $message)
    {
        foreach ($recipients as $recipient) {
            $this->send($recipient, $message);
        }
    }
}

class SmsNotificationService implements NotificationService
{
    public function send($to, $message)
    {
        // Implementación de SMS
        return "SMS enviado a {$to}: {$message}";
    }

    public function sendBulk($recipients, $message)
    {
        foreach ($recipients as $recipient) {
            $this->send($recipient, $message);
        }
    }
}

class PushNotificationService implements NotificationService
{
    public function send($to, $message)
    {
        // Implementación de Push Notification
        return "Push notification enviado a {$to}: {$message}";
    }

    public function sendBulk($recipients, $message)
    {
        foreach ($recipients as $recipient) {
            $this->send($recipient, $message);
        }
    }
}

class NotificationManager
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function sendWelcomeNotification($user)
    {
        $message = "¡Bienvenido {$user->name}!";
        return $this->notificationService->send($user->email, $message);
    }

    public function sendBulkNotification($users, $message)
    {
        $recipients = $users->pluck('email')->toArray();
        return $this->notificationService->sendBulk($recipients, $message);
    }
}

// Uso
$emailService = new EmailNotificationService();
$smsService = new SmsNotificationService();
$pushService = new PushNotificationService();

$emailManager = new NotificationManager($emailService);
$smsManager = new NotificationManager($smsService);
$pushManager = new NotificationManager($pushService);

$user = User::find(1);
$emailManager->sendWelcomeNotification($user);  // Email
$smsManager->sendWelcomeNotification($user);    // SMS
$pushManager->sendWelcomeNotification($user);   // Push
```

### 4. Polimorfismo en Repositorios

```php
interface RepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}

class UserRepository implements RepositoryInterface
{
    public function all()
    {
        return User::with('posts')->get();
    }

    public function find($id)
    {
        return User::with('posts')->find($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        return $user->delete();
    }
}

class PostRepository implements RepositoryInterface
{
    public function all()
    {
        return Post::with('user', 'comments')->get();
    }

    public function find($id)
    {
        return Post::with('user', 'comments')->find($id);
    }

    public function create(array $data)
    {
        return Post::create($data);
    }

    public function update($id, array $data)
    {
        $post = Post::findOrFail($id);
        $post->update($data);
        return $post;
    }

    public function delete($id)
    {
        $post = Post::findOrFail($id);
        return $post->delete();
    }
}

class GenericService
{
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getById($id)
    {
        return $this->repository->find($id);
    }

    public function create($data)
    {
        return $this->repository->create($data);
    }

    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}

// Uso
$userRepository = new UserRepository();
$postRepository = new PostRepository();

$userService = new GenericService($userRepository);
$postService = new GenericService($postRepository);

$users = $userService->getAll();  // Usa UserRepository
$posts = $postService->getAll();  // Usa PostRepository
```

### 5. Polimorfismo en Middleware

```php
interface LoggingMiddleware
{
    public function log($request, $response);
}

class ApiLoggingMiddleware implements LoggingMiddleware
{
    public function log($request, $response)
    {
        Log::info('API Request', [
            'method' => $request->method(),
            'url' => $request->url(),
            'status' => $response->status(),
            'user_id' => auth()->id()
        ]);
    }
}

class AdminLoggingMiddleware implements LoggingMiddleware
{
    public function log($request, $response)
    {
        if (auth()->check() && auth()->user()->is_admin) {
            Log::info('Admin Action', [
                'method' => $request->method(),
                'url' => $request->url(),
                'admin_id' => auth()->id(),
                'ip' => $request->ip()
            ]);
        }
    }
}

class LoggingHandler
{
    private $loggingMiddleware;

    public function __construct(LoggingMiddleware $loggingMiddleware)
    {
        $this->loggingMiddleware = $loggingMiddleware;
    }

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $this->loggingMiddleware->log($request, $response);
        
        return $response;
    }
}
```

### 6. Polimorfismo en Validación

```php
interface ValidatorInterface
{
    public function validate(array $data);
    public function getErrors();
    public function passes();
}

class UserValidator implements ValidatorInterface
{
    private $errors = [];

    public function validate(array $data)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];

        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            $this->errors = $validator->errors();
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function passes()
    {
        return empty($this->errors);
    }
}

class PostValidator implements ValidatorInterface
{
    private $errors = [];

    public function validate(array $data)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'user_id' => 'required|exists:users,id'
        ];

        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            $this->errors = $validator->errors();
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function passes()
    {
        return empty($this->errors);
    }
}

class ValidationService
{
    public function validate(ValidatorInterface $validator, array $data)
    {
        $validator->validate($data);
        
        if (!$validator->passes()) {
            throw new ValidationException($validator->getErrors());
        }
        
        return $data;
    }
}
```

## Mejores Prácticas

### 1. Usar Interfaces para Contratos

```php
// ✅ Bueno - Interfaz clara
interface PaymentProcessor
{
    public function process($amount);
    public function refund($transactionId);
}

// ❌ Malo - Sin interfaz
class PaymentProcessor
{
    // Implementación directa
}
```

### 2. Mantener Responsabilidad Única

```php
// ✅ Bueno - Cada clase tiene una responsabilidad
interface Logger
{
    public function log($message);
}

interface Notifier
{
    public function notify($user, $message);
}

// ❌ Malo - Múltiples responsabilidades
interface Service
{
    public function log($message);
    public function notify($user, $message);
    public function validate($data);
    public function process($data);
}
```

### 3. Documentar Comportamientos

```php
/**
 * Interface for payment processing
 */
interface PaymentProcessor
{
    /**
     * Process a payment
     *
     * @param float $amount
     * @return string Transaction ID
     * @throws PaymentException
     */
    public function process($amount);

    /**
     * Refund a payment
     *
     * @param string $transactionId
     * @return bool Success status
     */
    public function refund($transactionId);
}
```

### 4. Usar Inyección de Dependencias

```php
// ✅ Bueno - Inyección de dependencias
class OrderService
{
    private $paymentProcessor;

    public function __construct(PaymentProcessor $paymentProcessor)
    {
        $this->paymentProcessor = $paymentProcessor;
    }
}

// ❌ Malo - Creación directa
class OrderService
{
    private $paymentProcessor;

    public function __construct()
    {
        $this->paymentProcessor = new CreditCardPayment();
    }
}
```

## Casos de Uso Comunes

### 1. Múltiples Implementaciones

```php
interface CacheInterface
class RedisCache implements CacheInterface
class FileCache implements CacheInterface
class MemoryCache implements CacheInterface
```

### 2. Estrategias Diferentes

```php
interface SortingStrategy
class BubbleSort implements SortingStrategy
class QuickSort implements SortingStrategy
class MergeSort implements SortingStrategy
```

### 3. Adaptadores

```php
interface PaymentGateway
class StripeGateway implements PaymentGateway
class PayPalGateway implements PaymentGateway
class BankGateway implements PaymentGateway
```

El polimorfismo es fundamental en Laravel para crear código flexible, mantenible y extensible que puede adaptarse a diferentes implementaciones sin cambiar la lógica principal. 