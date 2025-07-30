# Herencia en Laravel

## ¿Qué es la Herencia?

La herencia es un mecanismo de POO que permite crear una nueva clase basada en una clase existente, heredando sus propiedades y métodos.

## Características Principales

- **Reutilización**: La clase hija hereda métodos y propiedades de la clase padre
- **Extensibilidad**: Se pueden agregar nuevos métodos y propiedades
- **Sobrescritura**: Se pueden modificar métodos heredados
- **Jerarquía**: Crea una estructura de clases relacionadas

## Sintaxis Básica

```php
class Animal
{
    protected $name;
    protected $species;

    public function __construct($name, $species)
    {
        $this->name = $name;
        $this->species = $species;
    }

    public function makeSound()
    {
        return "Hace un sonido";
    }

    public function getName()
    {
        return $this->name;
    }
}

class Dog extends Animal
{
    public function makeSound()
    {
        return "Guau guau!";
    }

    public function fetch()
    {
        return "{$this->name} está trayendo la pelota";
    }
}

// Uso
$dog = new Dog("Rex", "Canis");
echo $dog->makeSound(); // "Guau guau!"
echo $dog->getName(); // "Rex"
echo $dog->fetch(); // "Rex está trayendo la pelota"
```

## Herencia en Laravel

### 1. Herencia de Modelos

```php
class BaseModel extends Model
{
    protected $guarded = [];
    
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_by = auth()->id();
        });
        
        static::updating(function ($model) {
            $model->updated_by = auth()->id();
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }
}

class User extends BaseModel
{
    protected $fillable = ['name', 'email', 'password'];
    
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends BaseModel
{
    protected $fillable = ['title', 'content', 'user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Uso
$users = User::active()->recent()->get();
$posts = Post::active()->recent()->get();
```

### 2. Herencia de Controladores

```php
class BaseController extends Controller
{
    protected function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    protected function notFoundResponse($message = 'Resource not found')
    {
        return $this->errorResponse($message, 404);
    }

    protected function validationErrorResponse($errors)
    {
        return $this->errorResponse('Validation failed', 422, $errors);
    }
}

class UserController extends BaseController
{
    public function index()
    {
        $users = User::all();
        return $this->successResponse($users, 'Users retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::create($request->validated());
        return $this->successResponse($user, 'User created successfully', 201);
    }

    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        return $this->successResponse($user);
    }
}
```

### 3. Herencia de Requests

```php
class BaseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403)
        );
    }
}

class CreateUserRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El email es requerido',
            'email.unique' => 'El email ya está registrado',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ];
    }
}

class UpdateUserRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->user->id,
            'password' => 'sometimes|min:6|confirmed'
        ];
    }
}
```

### 4. Herencia de Middleware

```php
class BaseMiddleware
{
    protected function logRequest($request)
    {
        Log::info('Request', [
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }

    protected function logResponse($response)
    {
        Log::info('Response', [
            'status' => $response->status(),
            'content_type' => $response->headers->get('content-type')
        ]);
    }
}

class ApiLoggingMiddleware extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        $this->logRequest($request);
        
        $response = $next($request);
        
        $this->logResponse($response);
        
        return $response;
    }
}

class AdminLoggingMiddleware extends BaseMiddleware
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && auth()->user()->is_admin) {
            $this->logRequest($request);
        }
        
        $response = $next($request);
        
        if (auth()->check() && auth()->user()->is_admin) {
            $this->logResponse($response);
        }
        
        return $response;
    }
}
```

## Herencia Múltiple con Traits

```php
trait Timestampable
{
    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y H:i') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y H:i') : null;
    }
}

trait Searchable
{
    public function scopeSearch($query, $term)
    {
        return $query->where('title', 'like', "%{$term}%")
                    ->orWhere('content', 'like', "%{$term}%");
    }
}

class BaseModel extends Model
{
    use Timestampable, Searchable;

    protected $guarded = [];
}

class Post extends BaseModel
{
    protected $fillable = ['title', 'content', 'user_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

class Comment extends BaseModel
{
    protected $fillable = ['content', 'user_id', 'post_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

## Herencia de Servicios

```php
abstract class BaseService
{
    protected $model;
    protected $validator;

    public function __construct($model = null)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model::all();
    }

    public function find($id)
    {
        return $this->model::find($id);
    }

    public function findOrFail($id)
    {
        return $this->model::findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model::create($data);
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

    protected function validate(array $data, array $rules = [])
    {
        $validator = Validator::make($data, $rules);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        return $data;
    }
}

class UserService extends BaseService
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    public function create(array $data)
    {
        $this->validate($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $data['password'] = Hash::make($data['password']);
        
        return parent::create($data);
    }

    public function update($id, array $data)
    {
        $this->validate($data, [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'password' => 'sometimes|min:6'
        ]);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return parent::update($id, $data);
    }

    public function findByEmail($email)
    {
        return $this->model::where('email', $email)->first();
    }

    public function getActiveUsers()
    {
        return $this->model::where('status', 'active')->get();
    }
}

class PostService extends BaseService
{
    public function __construct()
    {
        parent::__construct(Post::class);
    }

    public function create(array $data)
    {
        $this->validate($data, [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id'
        ]);

        return parent::create($data);
    }

    public function getByUser($userId)
    {
        return $this->model::where('user_id', $userId)->get();
    }

    public function search($term)
    {
        return $this->model::search($term)->get();
    }
}
```

## Herencia de Repositorios

```php
abstract class BaseRepository
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model::all();
    }

    public function find($id)
    {
        return $this->model::find($id);
    }

    public function findOrFail($id)
    {
        return $this->model::findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model::create($data);
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
        return $this->model::paginate($perPage);
    }
}

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(User::class);
    }

    public function findByEmail($email)
    {
        return $this->model::where('email', $email)->first();
    }

    public function getActiveUsers()
    {
        return $this->model::where('status', 'active')->get();
    }

    public function getUsersWithPosts()
    {
        return $this->model::with('posts')->get();
    }
}

class PostRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(Post::class);
    }

    public function getByUser($userId)
    {
        return $this->model::where('user_id', $userId)->get();
    }

    public function getPublishedPosts()
    {
        return $this->model::where('status', 'published')->get();
    }

    public function search($term)
    {
        return $this->model::search($term)->get();
    }
}
```

## Herencia de Excepciones

```php
class BaseException extends Exception
{
    protected $context;

    public function __construct($message = "", $code = 0, $context = [])
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'context' => $this->getContext()
        ], 500);
    }
}

class UserNotFoundException extends BaseException
{
    public function __construct($userId)
    {
        parent::__construct("User with ID {$userId} not found", 404, ['user_id' => $userId]);
    }
}

class ValidationException extends BaseException
{
    public function __construct($errors)
    {
        parent::__construct("Validation failed", 422, ['errors' => $errors]);
    }
}

class InsufficientPermissionsException extends BaseException
{
    public function __construct($action)
    {
        parent::__construct("Insufficient permissions for action: {$action}", 403, ['action' => $action]);
    }
}
```

## Mejores Prácticas

### 1. Usar Herencia para Relaciones Jerárquicas

```php
// ✅ Bueno - Relación jerárquica clara
class Animal
class Dog extends Animal
class Cat extends Animal

// ❌ Malo - Herencia innecesaria
class User
class AdminUser extends User // Mejor usar roles/permissions
```

### 2. Evitar Herencia Profunda

```php
// ✅ Bueno - Herencia simple
class BaseModel extends Model
class User extends BaseModel

// ❌ Malo - Herencia muy profunda
class BaseModel extends Model
class TimestampedModel extends BaseModel
class UserModel extends TimestampedModel
class AdminUserModel extends UserModel
```

### 3. Usar Traits para Funcionalidades Compartidas

```php
// ✅ Bueno - Usar traits para funcionalidades
trait Timestampable
trait Searchable
trait Cacheable

class Post extends Model
{
    use Timestampable, Searchable, Cacheable;
}

// ❌ Malo - Herencia solo para funcionalidades
class TimestampedModel extends Model
class SearchableModel extends TimestampedModel
class Post extends SearchableModel
```

### 4. Documentar la Herencia

```php
/**
 * Base model with common functionality
 * 
 * Provides:
 * - Timestamp formatting
 * - Search functionality
 * - Audit logging
 */
class BaseModel extends Model
{
    // Implementation...
}

/**
 * User model extending base functionality
 * 
 * Additional features:
 * - Authentication methods
 * - Role management
 */
class User extends BaseModel
{
    // Implementation...
}
```

## Casos de Uso Comunes

### 1. Modelos Base

```php
class BaseModel extends Model
class User extends BaseModel
class Post extends BaseModel
class Comment extends BaseModel
```

### 2. Controladores Base

```php
class BaseController extends Controller
class UserController extends BaseController
class PostController extends BaseController
```

### 3. Servicios Base

```php
abstract class BaseService
class UserService extends BaseService
class PostService extends BaseService
```

### 4. Repositorios Base

```php
abstract class BaseRepository
class UserRepository extends BaseRepository
class PostRepository extends BaseRepository
```

La herencia es fundamental en Laravel para crear jerarquías de clases y compartir funcionalidades comunes de manera organizada y mantenible. 