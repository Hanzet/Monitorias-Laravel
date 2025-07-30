# Fundamentos de POO en Laravel

## Introducción

La Programación Orientada a Objetos (POO) es el paradigma fundamental que sustenta Laravel. Todos los componentes de Laravel están construidos siguiendo principios de POO, lo que hace que el framework sea modular, extensible y mantenible.

## 1. Clases y Objetos

### ¿Qué es una Clase?

Una clase es una plantilla o molde que define las propiedades y comportamientos que tendrán los objetos creados a partir de ella.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Propiedades de la clase
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    // Métodos de la clase
    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->email;
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
```

### ¿Qué es un Objeto?

Un objeto es una instancia específica de una clase. En Laravel, cuando creas un modelo, estás creando un objeto:

```php
// Crear un objeto User
$user = new User();
$user->name = 'Juan Pérez';
$user->email = 'juan@example.com';
$user->save();

// O usando el método create
$user = User::create([
    'name' => 'María García',
    'email' => 'maria@example.com',
    'password' => bcrypt('password123')
]);
```

## 2. Propiedades y Métodos

### Propiedades (Atributos)

Las propiedades almacenan datos del objeto. En Laravel, las propiedades más comunes son:

```php
class Post extends Model
{
    // Propiedades de configuración
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'content', 'user_id'];
    protected $guarded = ['id'];
    protected $hidden = ['password'];
    protected $visible = ['name', 'email'];

    // Propiedades de casting
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'metadata' => 'array'
    ];

    // Propiedades de fechas
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
}
```

### Métodos

Los métodos definen el comportamiento del objeto:

```php
class Post extends Model
{
    // Método de instancia
    public function isPublished()
    {
        return $this->status === 'published';
    }

    // Método estático
    public static function getPublishedPosts()
    {
        return static::where('status', 'published')->get();
    }

    // Método con parámetros
    public function updateStatus($status)
    {
        $this->status = $status;
        $this->save();
    }

    // Método que retorna un valor
    public function getExcerpt($length = 100)
    {
        return Str::limit($this->content, $length);
    }
}
```

## 3. Encapsulamiento

El encapsulamiento protege los datos y métodos del acceso no autorizado.

### Modificadores de Acceso

```php
class User extends Model
{
    // Público - accesible desde cualquier lugar
    public $name;

    // Protegido - accesible solo desde la clase y subclases
    protected $password;

    // Privado - accesible solo desde la clase
    private $secretKey;

    // Método público
    public function getName()
    {
        return $this->name;
    }

    // Método protegido
    protected function validatePassword($password)
    {
        return Hash::check($password, $this->password);
    }

    // Método privado
    private function generateSecretKey()
    {
        return Str::random(32);
    }
}
```

### Getters y Setters

```php
class User extends Model
{
    private $email;

    // Getter
    public function getEmail()
    {
        return $this->email;
    }

    // Setter con validación
    public function setEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
        } else {
            throw new InvalidArgumentException('Email inválido');
        }
    }

    // Accessor (Laravel)
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Mutator (Laravel)
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
```

## 4. Herencia

La herencia permite que una clase adquiera propiedades y métodos de otra clase.

### Herencia Simple

```php
// Clase base
class Model
{
    protected $fillable = [];
    protected $table;

    public function save()
    {
        // Lógica para guardar en base de datos
    }

    public function delete()
    {
        // Lógica para eliminar
    }
}

// Clase que hereda
class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    protected $table = 'users';

    // Método específico de User
    public function authenticate($password)
    {
        return Hash::check($password, $this->password);
    }
}
```

### Herencia en Laravel

```php
// Modelo base personalizado
abstract class BaseModel extends Model
{
    protected $guarded = ['id'];

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
}

// Modelo que hereda
class Post extends BaseModel
{
    protected $table = 'posts';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## 5. Polimorfismo

El polimorfismo permite que diferentes clases respondan de manera diferente al mismo método.

### Polimorfismo de Métodos

```php
// Interfaz común
interface Notifiable
{
    public function sendNotification($message);
}

// Diferentes implementaciones
class EmailNotification implements Notifiable
{
    public function sendNotification($message)
    {
        Mail::to($this->email)->send(new NotificationMail($message));
    }
}

class SMSNotification implements Notifiable
{
    public function sendNotification($message)
    {
        // Lógica para enviar SMS
    }
}

// Uso polimórfico
class NotificationService
{
    public function send(Notifiable $notifiable, $message)
    {
        $notifiable->sendNotification($message);
    }
}
```

### Polimorfismo en Eloquent

```php
// Relaciones polimórficas
class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}

class Post extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

class Video extends Model
{
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
```

## 6. Abstracción

La abstracción oculta la complejidad y muestra solo lo esencial.

### Clases Abstractas

```php
abstract class BaseController extends Controller
{
    abstract public function index();
    abstract public function store(Request $request);

    // Método común para todas las subclases
    protected function respondWithSuccess($data, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    protected function respondWithError($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }
}

// Implementación concreta
class UserController extends BaseController
{
    public function index()
    {
        $users = User::all();
        return $this->respondWithSuccess($users, 'Users retrieved successfully');
    }

    public function store(Request $request)
    {
        $user = User::create($request->validated());
        return $this->respondWithSuccess($user, 'User created successfully');
    }
}
```

### Interfaces

```php
interface UserRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}

class EloquentUserRepository implements UserRepositoryInterface
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
```

## Ejemplos Prácticos

### Ejemplo 1: Clase de Servicio

```php
class UserService
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function createUser(array $data)
    {
        // Validación
        $this->validateUserData($data);

        // Lógica de negocio
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();

        // Crear usuario
        $user = $this->userRepository->create($data);

        // Enviar email de bienvenida
        event(new UserRegistered($user));

        return $user;
    }

    private function validateUserData(array $data)
    {
        if (empty($data['email'])) {
            throw new InvalidArgumentException('Email es requerido');
        }

        if ($this->userRepository->findByEmail($data['email'])) {
            throw new InvalidArgumentException('Email ya existe');
        }
    }
}
```

### Ejemplo 2: Modelo con Comportamiento Complejo

```php
class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id', 'status'];

    // Estados del post
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    // Métodos de instancia
    public function publish()
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = now();
        $this->save();

        event(new PostPublished($this));
    }

    public function archive()
    {
        $this->status = self::STATUS_ARCHIVED;
        $this->archived_at = now();
        $this->save();
    }

    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function canBeEditedBy(User $user)
    {
        return $this->user_id === $user->id || $user->isAdmin();
    }

    // Métodos estáticos
    public static function getPublishedPosts()
    {
        return static::where('status', self::STATUS_PUBLISHED)
                    ->orderBy('published_at', 'desc')
                    ->get();
    }

    public static function getPostsByUser($userId)
    {
        return static::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    // Accessors y Mutators
    public function getExcerptAttribute()
    {
        return Str::limit($this->content, 150);
    }

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = ucfirst($value);
        $this->attributes['slug'] = Str::slug($value);
    }
}
```

## Mejores Prácticas

1. **Usa nombres descriptivos** para clases, métodos y propiedades
2. **Mantén las clases pequeñas** y con una responsabilidad única
3. **Utiliza modificadores de acceso** apropiados
4. **Documenta tus clases** con comentarios claros
5. **Sigue los principios SOLID**
6. **Usa interfaces** para definir contratos
7. **Implementa testing** para tus clases
8. **Mantén la consistencia** en el estilo de código

## Conclusión

Los fundamentos de POO son esenciales para desarrollar aplicaciones Laravel robustas y mantenibles. Al comprender estos conceptos, podrás crear código más organizado, reutilizable y escalable.
