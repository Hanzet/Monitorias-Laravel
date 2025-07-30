# Traits en Laravel

## ¿Qué son los Traits?

Los traits son un mecanismo de reutilización de código en PHP que permite compartir métodos entre clases sin usar herencia múltiple.

## Características Principales

- **Reutilización**: Permite compartir métodos entre clases
- **Composición**: Se pueden combinar múltiples traits en una clase
- **Sin herencia**: No requiere herencia de clase
- **Flexibilidad**: Se pueden usar en cualquier clase

## Sintaxis Básica

```php
trait Loggable
{
    public function log($message)
    {
        Log::info($message);
    }

    public function logError($message)
    {
        Log::error($message);
    }
}

class User
{
    use Loggable;

    public function create($data)
    {
        $this->log("Creando usuario: " . $data['email']);
        // Lógica de creación
    }
}

class Post
{
    use Loggable;

    public function publish()
    {
        $this->log("Publicando post: " . $this->title);
        // Lógica de publicación
    }
}
```

## Traits Comunes en Laravel

### 1. Notifiable Trait

```php
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Ahora la clase tiene métodos como:
    // - routeNotificationFor()
    // - notifications()
    // - readNotifications()
    // - unreadNotifications()
}

// Uso
$user = User::find(1);
$user->notify(new WelcomeNotification());
```

### 2. HasFactory Trait

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    // Permite usar factories
}

// Uso
$user = User::factory()->create();
$users = User::factory()->count(5)->create();
```

### 3. SoftDeletes Trait

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
}

// Uso
$post = Post::find(1);
$post->delete(); // Soft delete
$post->restore(); // Restaurar
$post->forceDelete(); // Eliminar permanentemente
```

## Creando Traits Personalizados

### 1. Trait para Timestamps

```php
trait HasTimestamps
{
    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y H:i') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d/m/Y H:i') : null;
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDays(7));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}

class Post extends Model
{
    use HasTimestamps;

    // Ahora tiene métodos para formatear fechas y scopes
}

// Uso
$post = Post::find(1);
echo $post->created_at; // Formato personalizado
$recentPosts = Post::recent()->get();
```

### 2. Trait para Slugs

```php
trait HasSlug
{
    public static function bootHasSlug()
    {
        static::creating(function ($model) {
            $model->generateSlug();
        });

        static::updating(function ($model) {
            if ($model->isDirty('title')) {
                $model->generateSlug();
            }
        });
    }

    public function generateSlug()
    {
        $slug = Str::slug($this->title);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $this->slug = $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}

class Post extends Model
{
    use HasSlug;

    protected $fillable = ['title', 'content', 'slug'];
}

// Uso
$post = Post::create(['title' => 'Mi Primer Post']);
echo $post->slug; // "mi-primer-post"
```

### 3. Trait para Cache

```php
trait Cacheable
{
    public function getCacheKey($suffix = '')
    {
        return sprintf(
            "%s/%s-%s%s",
            get_class($this),
            $this->getKey(),
            $this->updated_at->timestamp,
            $suffix
        );
    }

    public function cache($key, $callback, $ttl = 3600)
    {
        $cacheKey = $this->getCacheKey($key);
        
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    public function forgetCache($key = null)
    {
        if ($key) {
            Cache::forget($this->getCacheKey($key));
        } else {
            Cache::flush();
        }
    }
}

class Post extends Model
{
    use Cacheable;

    public function getCommentsCount()
    {
        return $this->cache('comments_count', function () {
            return $this->comments()->count();
        });
    }
}

// Uso
$post = Post::find(1);
$count = $post->getCommentsCount(); // Cacheado
$post->forgetCache('comments_count'); // Limpiar cache
```

### 4. Trait para Validación

```php
trait Validatable
{
    public function validate($data, $rules = null)
    {
        $rules = $rules ?: $this->getValidationRules();
        
        return Validator::make($data, $rules);
    }

    public function getValidationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id'
        ];
    }

    public function validateAndCreate($data)
    {
        $validator = $this->validate($data);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return static::create($data);
    }
}

class Post extends Model
{
    use Validatable;

    public function getValidationRules()
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:10',
            'user_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id'
        ];
    }
}

// Uso
$post = new Post();
$validator = $post->validate($data);
$post = Post::validateAndCreate($data);
```

### 5. Trait para Archivos

```php
trait HasFile
{
    public function uploadFile($file, $path = 'uploads')
    {
        if (!$file || !$file->isValid()) {
            return false;
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $filepath = $file->storeAs($path, $filename, 'public');

        $this->file_path = $filepath;
        $this->file_name = $filename;
        $this->file_size = $file->getSize();
        $this->file_type = $file->getMimeType();

        return $this->save();
    }

    public function deleteFile()
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }

        $this->file_path = null;
        $this->file_name = null;
        $this->file_size = null;
        $this->file_type = null;

        return $this->save();
    }

    public function getFileUrl()
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }
}

class Document extends Model
{
    use HasFile;

    protected $fillable = ['title', 'file_path', 'file_name', 'file_size', 'file_type'];
}

// Uso
$document = new Document(['title' => 'Mi Documento']);
$document->uploadFile($request->file('document'));
echo $document->getFileUrl();
```

## Traits con Propiedades

```php
trait Searchable
{
    protected $searchableFields = [];
    protected $searchableRelations = [];

    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        $query->where(function ($q) use ($term) {
            foreach ($this->searchableFields as $field) {
                $q->orWhere($field, 'like', "%{$term}%");
            }
        });

        if (!empty($this->searchableRelations)) {
            $query->with($this->searchableRelations);
        }

        return $query;
    }

    public function setSearchableFields(array $fields)
    {
        $this->searchableFields = $fields;
        return $this;
    }

    public function setSearchableRelations(array $relations)
    {
        $this->searchableRelations = $relations;
        return $this;
    }
}

class Post extends Model
{
    use Searchable;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        
        $this->setSearchableFields(['title', 'content'])
             ->setSearchableRelations(['user', 'category']);
    }
}

// Uso
$posts = Post::search('laravel')->get();
```

## Traits con Métodos Estáticos

```php
trait ApiResponse
{
    public static function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function errorResponse($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    public static function notFoundResponse($message = 'Resource not found')
    {
        return self::errorResponse($message, 404);
    }

    public static function validationErrorResponse($errors)
    {
        return self::errorResponse('Validation failed', 422, $errors);
    }
}

class PostController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $posts = Post::all();
        return self::successResponse($posts, 'Posts retrieved successfully');
    }

    public function show($id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            return self::notFoundResponse('Post not found');
        }

        return self::successResponse($post);
    }
}
```

## Traits con Conflictos

```php
trait Loggable
{
    public function log($message)
    {
        Log::info($message);
    }
}

trait Debuggable
{
    public function log($message)
    {
        dd($message); // Conflicto con Loggable
    }
}

class Post extends Model
{
    use Loggable, Debuggable {
        Loggable::log insteadof Debuggable; // Usar log de Loggable
        Debuggable::log as debugLog; // Renombrar método de Debuggable
    }
}

// Uso
$post = new Post();
$post->log('Mensaje normal'); // Usa Loggable::log
$post->debugLog('Debug info'); // Usa Debuggable::log
```

## Traits con Métodos Abstractos

```php
trait Validatable
{
    abstract public function getValidationRules(): array;

    public function validate($data)
    {
        $rules = $this->getValidationRules();
        return Validator::make($data, $rules);
    }
}

class Post extends Model
{
    use Validatable;

    public function getValidationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string'
        ];
    }
}
```

## Mejores Prácticas

### 1. Nombres Descriptivos

```php
// ✅ Bueno
trait HasTimestamps
trait Searchable
trait Cacheable
trait Validatable

// ❌ Malo
trait Helper
trait Utils
trait Common
```

### 2. Responsabilidad Única

```php
// ✅ Bueno - Un trait por funcionalidad
trait HasSlug
trait HasFile
trait HasTimestamps

// ❌ Malo - Un trait con múltiples responsabilidades
trait PostHelpers // Demasiado genérico
```

### 3. Documentación

```php
/**
 * Trait para manejo de archivos
 * 
 * Proporciona métodos para subir, eliminar y obtener URLs de archivos
 */
trait HasFile
{
    /**
     * Sube un archivo al storage
     *
     * @param UploadedFile $file
     * @param string $path
     * @return bool
     */
    public function uploadFile($file, $path = 'uploads')
    {
        // Implementación...
    }
}
```

### 4. Evitar Dependencias Complejas

```php
// ✅ Bueno - Dependencias simples
trait Loggable
{
    public function log($message)
    {
        Log::info($message);
    }
}

// ❌ Malo - Dependencias complejas
trait ComplexTrait
{
    public function complexMethod()
    {
        // Muchas dependencias y lógica compleja
        $this->service->process($this->repository->getData());
    }
}
```

## Casos de Uso Comunes

### 1. Funcionalidades Transversales

```php
trait SoftDeletes
trait Timestamps
trait Searchable
trait Cacheable
```

### 2. Comportamientos Específicos

```php
trait HasSlug
trait HasFile
trait HasImage
trait HasLocation
```

### 3. Utilidades

```php
trait ApiResponse
trait Validation
trait Logging
trait Debugging
```

Los traits son una herramienta poderosa en Laravel para compartir funcionalidades entre clases sin crear dependencias complejas o herencia múltiple. 