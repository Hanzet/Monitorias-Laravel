# Abstracción en Laravel

## ¿Qué es la Abstracción?

La abstracción es el proceso de ocultar la complejidad de la implementación y mostrar solo las características esenciales de un objeto, permitiendo trabajar con conceptos de alto nivel.

## Características Principales

-   **Ocultamiento**: Oculta detalles de implementación complejos
-   **Simplificación**: Proporciona una interfaz simple para operaciones complejas
-   **Reutilización**: Permite usar la misma abstracción en diferentes contextos
-   **Mantenibilidad**: Facilita cambios en la implementación sin afectar el código cliente

## Tipos de Abstracción

### 1. Abstracción de Datos

```php
abstract class DatabaseConnection
{
    abstract public function connect();
    abstract public function query($sql);
    abstract public function close();

    public function execute($sql)
    {
        $this->connect();
        $result = $this->query($sql);
        $this->close();
        return $result;
    }
}

class MySQLConnection extends DatabaseConnection
{
    private $connection;

    public function connect()
    {
        $this->connection = mysqli_connect('localhost', 'user', 'pass', 'db');
    }

    public function query($sql)
    {
        return mysqli_query($this->connection, $sql);
    }

    public function close()
    {
        mysqli_close($this->connection);
    }
}

class PostgreSQLConnection extends DatabaseConnection
{
    private $connection;

    public function connect()
    {
        $this->connection = pg_connect("host=localhost dbname=db user=user password=pass");
    }

    public function query($sql)
    {
        return pg_query($this->connection, $sql);
    }

    public function close()
    {
        pg_close($this->connection);
    }
}

// Uso - El código cliente no necesita conocer la implementación específica
$db = new MySQLConnection();
$result = $db->execute("SELECT * FROM users");
```

### 2. Abstracción de Procesos

```php
abstract class PaymentProcessor
{
    abstract protected function validatePayment($amount);
    abstract protected function processPayment($amount);
    abstract protected function generateReceipt($transactionId);

    public function handlePayment($amount)
    {
        $this->validatePayment($amount);
        $transactionId = $this->processPayment($amount);
        return $this->generateReceipt($transactionId);
    }
}

class CreditCardProcessor extends PaymentProcessor
{
    protected function validatePayment($amount)
    {
        if ($amount <= 0) {
            throw new Exception("Invalid amount");
        }
    }

    protected function processPayment($amount)
    {
        // Lógica compleja de procesamiento de tarjeta de crédito
        return "CC_" . uniqid();
    }

    protected function generateReceipt($transactionId)
    {
        return "Credit Card Receipt: {$transactionId}";
    }
}

class PayPalProcessor extends PaymentProcessor
{
    protected function validatePayment($amount)
    {
        if ($amount < 1) {
            throw new Exception("Minimum amount is $1");
        }
    }

    protected function processPayment($amount)
    {
        // Lógica compleja de procesamiento de PayPal
        return "PP_" . uniqid();
    }

    protected function generateReceipt($transactionId)
    {
        return "PayPal Receipt: {$transactionId}";
    }
}

// Uso - Interfaz simple para operaciones complejas
$processor = new CreditCardProcessor();
$receipt = $processor->handlePayment(100);
```

## Abstracción en Laravel

### 1. Abstracción de Modelos

```php
abstract class BaseModel extends Model
{
    protected $guarded = [];

    abstract public function getValidationRules();
    abstract public function getSearchableFields();

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    public function search($term)
    {
        $query = $this->newQuery();

        foreach ($this->getSearchableFields() as $field) {
            $query->orWhere($field, 'like', "%{$term}%");
        }

        return $query->get();
    }

    public function validateAndCreate($data)
    {
        $validator = Validator::make($data, $this->getValidationRules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->create($data);
    }
}

class User extends BaseModel
{
    protected $fillable = ['name', 'email', 'password'];

    public function getValidationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];
    }

    public function getSearchableFields()
    {
        return ['name', 'email'];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends BaseModel
{
    protected $fillable = ['title', 'content', 'user_id'];

    public function getValidationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'user_id' => 'required|exists:users,id'
        ];
    }

    public function getSearchableFields()
    {
        return ['title', 'content'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Uso - Interfaz consistente para todos los modelos
$user = new User();
$users = $user->search('john');
$newUser = $user->validateAndCreate($data);

$post = new Post();
$posts = $post->search('laravel');
$newPost = $post->validateAndCreate($data);
```

### 2. Abstracción de Controladores

```php
abstract class BaseController extends Controller
{
    abstract protected function getModel();
    abstract protected function getValidationRules();
    abstract protected function getResourceClass();

    public function index()
    {
        $items = $this->getModel()::paginate(15);
        return $this->successResponse($items);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $item = $this->getModel()::create($request->validated());
        return $this->successResponse($item, 'Created successfully', 201);
    }

    public function show($id)
    {
        $item = $this->getModel()::findOrFail($id);
        return $this->successResponse($item);
    }

    public function update(Request $request, $id)
    {
        $item = $this->getModel()::findOrFail($id);

        $validator = Validator::make($request->all(), $this->getValidationRules());

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $item->update($request->validated());
        return $this->successResponse($item);
    }

    public function destroy($id)
    {
        $item = $this->getModel()::findOrFail($id);
        $item->delete();
        return $this->successResponse(null, 'Deleted successfully', 204);
    }

    protected function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function validationErrorResponse($errors)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }
}

class UserController extends BaseController
{
    protected function getModel()
    {
        return User::class;
    }

    protected function getValidationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];
    }

    protected function getResourceClass()
    {
        return UserResource::class;
    }
}

class PostController extends BaseController
{
    protected function getModel()
    {
        return Post::class;
    }

    protected function getValidationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'user_id' => 'required|exists:users,id'
        ];
    }

    protected function getResourceClass()
    {
        return PostResource::class;
    }
}

// Uso - Todos los controladores tienen la misma interfaz
Route::apiResource('users', UserController::class);
Route::apiResource('posts', PostController::class);
```

### 3. Abstracción de Servicios

```php
abstract class BaseService
{
    abstract protected function getModel();
    abstract protected function getValidationRules();
    abstract protected function getRepository();

    public function getAll($filters = [])
    {
        return $this->getRepository()->getAll($filters);
    }

    public function findById($id)
    {
        return $this->getRepository()->findById($id);
    }

    public function create($data)
    {
        $this->validate($data);
        return $this->getRepository()->create($data);
    }

    public function update($id, $data)
    {
        $this->validate($data);
        return $this->getRepository()->update($id, $data);
    }

    public function delete($id)
    {
        return $this->getRepository()->delete($id);
    }

    protected function validate($data)
    {
        $validator = Validator::make($data, $this->getValidationRules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $data;
    }
}

class UserService extends BaseService
{
    protected function getModel()
    {
        return User::class;
    }

    protected function getValidationRules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];
    }

    protected function getRepository()
    {
        return app(UserRepository::class);
    }

    public function findByEmail($email)
    {
        return $this->getRepository()->findByEmail($email);
    }
}

class PostService extends BaseService
{
    protected function getModel()
    {
        return Post::class;
    }

    protected function getValidationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'user_id' => 'required|exists:users,id'
        ];
    }

    protected function getRepository()
    {
        return app(PostRepository::class);
    }

    public function getByUser($userId)
    {
        return $this->getRepository()->getByUser($userId);
    }
}
```

### 4. Abstracción de Repositorios

```php
abstract class BaseRepository
{
    abstract protected function getModel();

    public function all()
    {
        return $this->getModel()::all();
    }

    public function find($id)
    {
        return $this->getModel()::find($id);
    }

    public function findOrFail($id)
    {
        return $this->getModel()::findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->getModel()::create($data);
    }

    public function update($id, array $data)
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model;
    }

    public function delete($id)
    {
        $model = $this->findOrFail($id);
        return $model->delete();
    }

    public function paginate($perPage = 15)
    {
        return $this->getModel()::paginate($perPage);
    }

    public function where($column, $value)
    {
        return $this->getModel()::where($column, $value);
    }
}

class UserRepository extends BaseRepository
{
    protected function getModel()
    {
        return User::class;
    }

    public function findByEmail($email)
    {
        return $this->getModel()::where('email', $email)->first();
    }

    public function getActiveUsers()
    {
        return $this->getModel()::where('status', 'active')->get();
    }
}

class PostRepository extends BaseRepository
{
    protected function getModel()
    {
        return Post::class;
    }

    public function getByUser($userId)
    {
        return $this->getModel()::where('user_id', $userId)->get();
    }

    public function getPublishedPosts()
    {
        return $this->getModel()::where('status', 'published')->get();
    }
}
```

### 5. Abstracción de Middleware

```php
abstract class BaseMiddleware
{
    abstract protected function shouldProcess($request);
    abstract protected function processRequest($request);
    abstract protected function processResponse($response);

    public function handle($request, Closure $next)
    {
        if ($this->shouldProcess($request)) {
            $this->processRequest($request);
        }

        $response = $next($request);

        if ($this->shouldProcess($request)) {
            $this->processResponse($response);
        }

        return $response;
    }
}

class LoggingMiddleware extends BaseMiddleware
{
    protected function shouldProcess($request)
    {
        return config('app.debug');
    }

    protected function processRequest($request)
    {
        Log::info('Request', [
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip()
        ]);
    }

    protected function processResponse($response)
    {
        Log::info('Response', [
            'status' => $response->status()
        ]);
    }
}

class AdminLoggingMiddleware extends BaseMiddleware
{
    protected function shouldProcess($request)
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    protected function processRequest($request)
    {
        Log::info('Admin Request', [
            'admin_id' => auth()->id(),
            'method' => $request->method(),
            'url' => $request->url()
        ]);
    }

    protected function processResponse($response)
    {
        Log::info('Admin Response', [
            'admin_id' => auth()->id(),
            'status' => $response->status()
        ]);
    }
}
```

### 6. Abstracción de Validación

```php
abstract class BaseValidator
{
    abstract protected function getRules();
    abstract protected function getMessages();

    public function validate($data)
    {
        $validator = Validator::make($data, $this->getRules(), $this->getMessages());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $data;
    }

    public function validatePartial($data)
    {
        $rules = array_intersect_key($this->getRules(), $data);
        $validator = Validator::make($data, $rules, $this->getMessages());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $data;
    }
}

class UserValidator extends BaseValidator
{
    protected function getRules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ];
    }

    protected function getMessages()
    {
        return [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El email es requerido',
            'email.unique' => 'El email ya está registrado',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres'
        ];
    }
}

class PostValidator extends BaseValidator
{
    protected function getRules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'user_id' => 'required|exists:users,id'
        ];
    }

    protected function getMessages()
    {
        return [
            'title.required' => 'El título es requerido',
            'content.required' => 'El contenido es requerido',
            'content.min' => 'El contenido debe tener al menos 10 caracteres',
            'user_id.required' => 'El usuario es requerido',
            'user_id.exists' => 'El usuario no existe'
        ];
    }
}
```

## Mejores Prácticas

### 1. Mantener la Abstracción Simple

```php
// ✅ Bueno - Abstracción clara y simple
abstract class BaseModel
{
    abstract public function getValidationRules();

    public function validateAndCreate($data)
    {
        // Implementación simple
    }
}

// ❌ Malo - Abstracción compleja
abstract class BaseModel
{
    abstract public function getValidationRules();
    abstract public function getSearchableFields();
    abstract public function getSortableFields();
    abstract public function getFilterableFields();
    abstract public function getRelationships();
    // Demasiadas responsabilidades
}
```

### 2. Documentar la Abstracción

```php
/**
 * Base model with common functionality
 *
 * Provides:
 * - Validation
 * - Search functionality
 * - Common scopes
 */
abstract class BaseModel extends Model
{
    /**
     * Get validation rules for the model
     *
     * @return array
     */
    abstract public function getValidationRules();

    /**
     * Get searchable fields for the model
     *
     * @return array
     */
    abstract public function getSearchableFields();
}
```

### 3. Usar Métodos Template

```php
abstract class BaseService
{
    public function process($data)
    {
        $this->validate($data);
        $this->preProcess($data);
        $result = $this->execute($data);
        $this->postProcess($result);
        return $result;
    }

    abstract protected function execute($data);

    protected function validate($data) { /* Default implementation */ }
    protected function preProcess($data) { /* Default implementation */ }
    protected function postProcess($result) { /* Default implementation */ }
}
```

### 4. Evitar Abstracción Excesiva

```php
// ✅ Bueno - Abstracción útil
abstract class BaseController
{
    abstract protected function getModel();

    public function index()
    {
        return $this->getModel()::all();
    }
}

// ❌ Malo - Abstracción innecesaria
abstract class BaseController
{
    abstract protected function getModel();
    abstract protected function getIndexMethod();
    abstract protected function getIndexResponse();

    public function index()
    {
        $method = $this->getIndexMethod();
        return $this->getIndexResponse($this->getModel()::$method());
    }
}
```

## Casos de Uso Comunes

### 1. Operaciones CRUD

```php
abstract class BaseCrudService
{
    abstract protected function getModel();

    public function create($data) { /* Implementation */ }
    public function read($id) { /* Implementation */ }
    public function update($id, $data) { /* Implementation */ }
    public function delete($id) { /* Implementation */ }
}
```

### 2. Validación

```php
abstract class BaseValidator
{
    abstract protected function getRules();

    public function validate($data) { /* Implementation */ }
    public function validatePartial($data) { /* Implementation */ }
}
```

### 3. Autenticación

```php
abstract class BaseAuthService
{
    abstract protected function authenticate($credentials);
    abstract protected function generateToken($user);

    public function login($credentials) { /* Implementation */ }
    public function logout($user) { /* Implementation */ }
}
```

La abstracción es fundamental en Laravel para crear código reutilizable, mantenible y fácil de entender, ocultando la complejidad de implementación y proporcionando interfaces simples y consistentes.
