# Modelos Eloquent en Laravel

## Introducción

Los Modelos Eloquent son la representación orientada a objetos de las tablas de la base de datos en Laravel. Cada modelo representa una tabla y proporciona una interfaz elegante para interactuar con los datos.

## 1. Definición de Modelos

### Estructura Básica de un Modelo

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasFactory, SoftDeletes;

    // Propiedades de configuración
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    // Propiedades de asignación masiva
    protected $fillable = ['name', 'email', 'password'];
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Propiedades de ocultación
    protected $hidden = ['password', 'remember_token'];
    protected $visible = ['id', 'name', 'email', 'created_at'];

    // Propiedades de casting
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
        'metadata' => 'object'
    ];

    // Propiedades de fechas
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'email_verified_at'];

    // Propiedades de conexión
    protected $connection = 'mysql';
}
```

### Crear un Modelo con Artisan

```bash
# Crear modelo básico
php artisan make:model User

# Crear modelo con migración
php artisan make:model User -m

# Crear modelo con migración y seeder
php artisan make:model User -ms

# Crear modelo con migración, seeder y factory
php artisan make:model User -msf

# Crear modelo con controlador
php artisan make:model User -c

# Crear modelo con controlador y migración
php artisan make:model User -cm
```

## 2. Propiedades y Atributos

### Propiedades de Configuración

```php
class Post extends Model
{
    // Nombre de la tabla (por defecto: nombre del modelo en plural)
    protected $table = 'blog_posts';

    // Clave primaria (por defecto: 'id')
    protected $primaryKey = 'post_id';

    // Tipo de clave primaria
    protected $keyType = 'string'; // 'int' o 'string'

    // Si la clave primaria es autoincremental
    public $incrementing = false;

    // Si usa timestamps automáticos
    public $timestamps = false;

    // Nombres personalizados para timestamps
    const CREATED_AT = 'created_date';
    const UPDATED_AT = 'updated_date';

    // Conexión de base de datos
    protected $connection = 'pgsql';
}
```

### Propiedades de Asignación Masiva

```php
class User extends Model
{
    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address'
    ];

    // Campos que NO se pueden asignar masivamente
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
        'admin'
    ];

    // Permitir todos los campos excepto los especificados
    protected $guarded = ['id', 'admin'];

    // No permitir asignación masiva en ningún campo
    protected $guarded = ['*'];
}
```

### Propiedades de Ocultación

```php
class User extends Model
{
    // Campos que se ocultan en arrays/JSON
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
        'credit_card_number'
    ];

    // Campos que se muestran en arrays/JSON
    protected $visible = [
        'id',
        'name',
        'email',
        'created_at'
    ];

    // Campos adicionales que se incluyen en arrays/JSON
    protected $appends = [
        'full_name',
        'age',
        'profile_url'
    ];
}
```

### Propiedades de Casting

```php
class User extends Model
{
    protected $casts = [
        // Tipos básicos
        'is_active' => 'boolean',
        'age' => 'integer',
        'height' => 'float',
        'name' => 'string',

        // Fechas
        'birth_date' => 'date',
        'last_login' => 'datetime',
        'email_verified_at' => 'datetime:Y-m-d H:i:s',

        // Arrays y objetos
        'settings' => 'array',
        'metadata' => 'object',
        'preferences' => 'collection',

        // JSON
        'profile_data' => 'json',

        // Enums
        'status' => 'string',
        'role' => 'integer',

        // Timestamps
        'created_at' => 'datetime',
        'updated_at' => 'datetime',

        // Campos personalizados
        'amount' => 'decimal:2',
        'percentage' => 'decimal:4'
    ];
}
```

## 3. Métodos de Instancia

### Métodos Básicos

```php
class Post extends Model
{
    // Método para verificar si el post está publicado
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    // Método para publicar el post
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now()
        ]);
    }

    // Método para obtener el excerpt
    public function getExcerpt(int $length = 150): string
    {
        return Str::limit($this->content, $length);
    }

    // Método para verificar si el usuario puede editar
    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id || $user->isAdmin();
    }

    // Método para incrementar vistas
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    // Método para obtener URL del post
    public function getUrlAttribute(): string
    {
        return route('posts.show', $this->slug);
    }
}
```

### Métodos con Relaciones

```php
class User extends Model
{
    // Método para obtener posts recientes
    public function getRecentPosts(int $limit = 5)
    {
        return $this->posts()
                   ->where('status', 'published')
                   ->orderBy('created_at', 'desc')
                   ->limit($limit)
                   ->get();
    }

    // Método para obtener estadísticas
    public function getStats(): array
    {
        return [
            'total_posts' => $this->posts()->count(),
            'published_posts' => $this->posts()->where('status', 'published')->count(),
            'total_comments' => $this->comments()->count(),
            'last_post_date' => $this->posts()->max('created_at')
        ];
    }

    // Método para verificar si tiene posts
    public function hasPosts(): bool
    {
        return $this->posts()->exists();
    }

    // Método para obtener posts populares
    public function getPopularPosts(int $limit = 10)
    {
        return $this->posts()
                   ->where('status', 'published')
                   ->orderBy('views_count', 'desc')
                   ->limit($limit)
                   ->get();
    }
}
```

## 4. Métodos Estáticos

### Métodos de Consulta

```php
class Post extends Model
{
    // Obtener posts publicados
    public static function getPublishedPosts()
    {
        return static::where('status', 'published')
                    ->orderBy('published_at', 'desc')
                    ->get();
    }

    // Obtener posts por usuario
    public static function getPostsByUser(int $userId)
    {
        return static::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    // Buscar posts por título
    public static function searchByTitle(string $title)
    {
        return static::where('title', 'LIKE', "%{$title}%")
                    ->where('status', 'published')
                    ->get();
    }

    // Obtener posts populares
    public static function getPopularPosts(int $limit = 10)
    {
        return static::where('status', 'published')
                    ->orderBy('views_count', 'desc')
                    ->limit($limit)
                    ->get();
    }

    // Obtener estadísticas generales
    public static function getStats(): array
    {
        return [
            'total_posts' => static::count(),
            'published_posts' => static::where('status', 'published')->count(),
            'draft_posts' => static::where('status', 'draft')->count(),
            'total_views' => static::sum('views_count')
        ];
    }
}
```

### Métodos de Creación

```php
class User extends Model
{
    // Crear usuario con validación
    public static function createUser(array $data): User
    {
        // Validar datos
        if (empty($data['email'])) {
            throw new \InvalidArgumentException('Email es requerido');
        }

        // Verificar email único
        if (static::where('email', $data['email'])->exists()) {
            throw new \InvalidArgumentException('Email ya existe');
        }

        // Hash password
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Crear usuario
        $user = static::create($data);

        // Enviar email de bienvenida
        event(new UserRegistered($user));

        return $user;
    }

    // Crear usuario administrador
    public static function createAdmin(array $data): User
    {
        $data['is_admin'] = true;
        $data['email_verified_at'] = now();

        return static::createUser($data);
    }
}
```

## 5. Relaciones entre Modelos

### Relaciones Básicas

```php
class User extends Model
{
    // Uno a muchos - Un usuario tiene muchos posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // Uno a muchos - Un usuario tiene muchos comentarios
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Uno a uno - Un usuario tiene un perfil
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

class Post extends Model
{
    // Muchos a uno - Un post pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Uno a muchos - Un post tiene muchos comentarios
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Muchos a muchos - Un post tiene muchos tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}

class Comment extends Model
{
    // Muchos a uno - Un comentario pertenece a un usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Muchos a uno - Un comentario pertenece a un post
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

### Relaciones Avanzadas

```php
class Post extends Model
{
    // Relación con tabla pivot personalizada
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags', 'post_id', 'tag_id')
                    ->withTimestamps()
                    ->withPivot('created_at');
    }

    // Relación polimórfica - Comentarios en posts y videos
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Relación a través de otra tabla
    public function followers()
    {
        return $this->hasManyThrough(User::class, Follow::class, 'followed_id', 'id', 'id', 'follower_id');
    }

    // Relación con condiciones
    public function approvedComments()
    {
        return $this->hasMany(Comment::class)->where('is_approved', true);
    }

    // Relación con ordenamiento
    public function recentComments()
    {
        return $this->hasMany(Comment::class)
                    ->orderBy('created_at', 'desc')
                    ->limit(5);
    }
}
```

### Relaciones con Eager Loading

```php
class User extends Model
{
    // Cargar relaciones automáticamente
    protected $with = ['profile'];

    // Cargar relaciones condicionalmente
    public function posts()
    {
        return $this->hasMany(Post::class)->with(['comments', 'tags']);
    }

    // Relación con contador
    public function postsWithCount()
    {
        return $this->hasMany(Post::class)->withCount(['comments', 'likes']);
    }

    // Relación con suma
    public function postsWithSum()
    {
        return $this->hasMany(Post::class)->withSum('views', 'count');
    }
}
```

## 6. Accessors y Mutators

### Accessors (Getters)

```php
class User extends Model
{
    // Accessor básico
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Accessor con lógica condicional
    public function getDisplayNameAttribute(): string
    {
        return $this->nickname ?: $this->full_name;
    }

    // Accessor para URL de avatar
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return 'https://www.gravatar.com/avatar/' . md5($this->email) . '?d=mp';
    }

    // Accessor para edad
    public function getAgeAttribute(): int
    {
        if (!$this->birth_date) {
            return 0;
        }

        return Carbon::parse($this->birth_date)->age;
    }

    // Accessor para estado de verificación
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->email_verified_at);
    }
}
```

### Mutators (Setters)

```php
class User extends Model
{
    // Mutator básico
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    // Mutator para nombre
    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = ucfirst(strtolower($value));
    }

    // Mutator para email
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Mutator para teléfono
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = preg_replace('/[^0-9]/', '', $value);
    }

    // Mutator para configuración
    public function setSettingsAttribute($value): void
    {
        $this->attributes['settings'] = json_encode($value);
    }
}
```

## 7. Scopes (Ámbitos)

### Scopes Locales

```php
class Post extends Model
{
    // Scope para posts publicados
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Scope para posts por usuario
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope para búsqueda por título
    public function scopeSearch($query, $term)
    {
        return $query->where('title', 'LIKE', "%{$term}%");
    }

    // Scope para posts recientes
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Scope para posts populares
    public function scopePopular($query, $minViews = 100)
    {
        return $query->where('views_count', '>=', $minViews);
    }

    // Scope con múltiples parámetros
    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query;
    }
}
```

### Scopes Globales

```php
class Post extends Model
{
    protected static function booted()
    {
        // Scope global para solo posts publicados
        static::addGlobalScope('published', function ($query) {
            $query->where('status', 'published');
        });

        // Scope global para posts del usuario autenticado
        static::addGlobalScope('user', function ($query) {
            if (auth()->check()) {
                $query->where('user_id', auth()->id());
            }
        });
    }

    // Remover scope global
    public static function withoutGlobalScopes()
    {
        return static::withoutGlobalScope('published');
    }
}
```

## 8. Eventos de Modelo

### Eventos Básicos

```php
class User extends Model
{
    protected static function booted()
    {
        // Antes de crear
        static::creating(function ($user) {
            $user->email_verified_at = now();
        });

        // Después de crear
        static::created(function ($user) {
            event(new UserRegistered($user));
        });

        // Antes de actualizar
        static::updating(function ($user) {
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
        });

        // Después de actualizar
        static::updated(function ($user) {
            if ($user->wasChanged('email')) {
                event(new UserEmailChanged($user));
            }
        });

        // Antes de eliminar
        static::deleting(function ($user) {
            if ($user->posts()->count() > 0) {
                throw new \Exception('No se puede eliminar usuario con posts');
            }
        });

        // Después de eliminar
        static::deleted(function ($user) {
            event(new UserDeleted($user));
        });
    }
}
```

### Eventos Personalizados

```php
class Post extends Model
{
    protected static function booted()
    {
        // Evento personalizado para publicación
        static::saving(function ($post) {
            if ($post->isDirty('status') && $post->status === 'published') {
                $post->published_at = now();
            }
        });

        // Evento para incrementar contador de posts del usuario
        static::created(function ($post) {
            $post->user()->increment('posts_count');
        });

        static::deleted(function ($post) {
            $post->user()->decrement('posts_count');
        });
    }
}
```

## 9. Ejemplos Prácticos

### Modelo User Completo

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class User extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'avatar',
        'settings',
        'is_admin',
        'email_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    protected $appends = [
        'full_name',
        'display_name',
        'avatar_url',
        'age',
        'is_verified'
    ];

    // Relaciones
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'followed_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'followed_id');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->nickname ?: $this->full_name;
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }

        return 'https://www.gravatar.com/avatar/' . md5($this->email) . '?d=mp';
    }

    public function getAgeAttribute(): int
    {
        if (!$this->birth_date) {
            return 0;
        }

        return Carbon::parse($this->birth_date)->age;
    }

    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->email_verified_at);
    }

    // Mutators
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function setFirstNameAttribute($value): void
    {
        $this->attributes['first_name'] = ucfirst(strtolower($value));
    }

    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower($value);
    }

    // Métodos de instancia
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function verifyEmail(): void
    {
        $this->update(['email_verified_at' => now()]);
    }

    public function getStats(): array
    {
        return [
            'total_posts' => $this->posts()->count(),
            'published_posts' => $this->posts()->where('status', 'published')->count(),
            'total_comments' => $this->comments()->count(),
            'followers_count' => $this->followers()->count(),
            'following_count' => $this->following()->count()
        ];
    }

    public function canEditPost(Post $post): bool
    {
        return $this->id === $post->user_id || $this->isAdmin();
    }

    // Métodos estáticos
    public static function createUser(array $data): User
    {
        if (static::where('email', $data['email'])->exists()) {
            throw new \InvalidArgumentException('Email ya existe');
        }

        $user = static::create($data);
        event(new UserRegistered($user));

        return $user;
    }

    public static function getAdmins()
    {
        return static::where('is_admin', true)->get();
    }

    public static function getVerifiedUsers()
    {
        return static::whereNotNull('email_verified_at')->get();
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'LIKE', "%{$term}%")
              ->orWhere('last_name', 'LIKE', "%{$term}%")
              ->orWhere('email', 'LIKE', "%{$term}%");
        });
    }

    // Eventos
    protected static function booted()
    {
        static::creating(function ($user) {
            if (!isset($user->email_verified_at)) {
                $user->email_verified_at = now();
            }
        });

        static::created(function ($user) {
            event(new UserRegistered($user));
        });

        static::updated(function ($user) {
            if ($user->wasChanged('email')) {
                event(new UserEmailChanged($user));
            }
        });
    }
}
```

## 10. Mejores Prácticas

### 1. Organización de Propiedades

```php
class User extends Model
{
    // 1. Propiedades de configuración
    protected $table = 'users';
    protected $primaryKey = 'id';

    // 2. Propiedades de asignación
    protected $fillable = ['name', 'email'];
    protected $guarded = ['id'];

    // 3. Propiedades de ocultación
    protected $hidden = ['password'];
    protected $visible = ['name', 'email'];
    protected $appends = ['full_name'];

    // 4. Propiedades de casting
    protected $casts = ['is_admin' => 'boolean'];

    // 5. Propiedades de fechas
    protected $dates = ['created_at', 'updated_at'];
}
```

### 2. Nomenclatura de Métodos

```php
class Post extends Model
{
    // Métodos de consulta
    public function scopePublished($query) { /* ... */ }
    public function scopeByUser($query, $userId) { /* ... */ }

    // Métodos de instancia
    public function isPublished(): bool { /* ... */ }
    public function publish(): void { /* ... */ }
    public function canBeEditedBy(User $user): bool { /* ... */ }

    // Métodos estáticos
    public static function getPublishedPosts() { /* ... */ }
    public static function createPost(array $data) { /* ... */ }

    // Accessors y Mutators
    public function getExcerptAttribute(): string { /* ... */ }
    public function setTitleAttribute($value): void { /* ... */ }
}
```

### 3. Documentación

```php
/**
 * Modelo User para gestionar usuarios del sistema
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property bool $is_admin
 * @property \Carbon\Carbon $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read string $full_name
 * @property-read string $avatar_url
 * @property-read int $age
 * @property-read bool $is_verified
 *
 * @method static \Illuminate\Database\Eloquent\Builder admins()
 * @method static \Illuminate\Database\Eloquent\Builder verified()
 * @method static \Illuminate\Database\Eloquent\Builder search(string $term)
 */
class User extends Model
{
    // Implementación...
}
```

## Conclusión

Los Modelos Eloquent son el corazón de Laravel y proporcionan una interfaz elegante para trabajar con la base de datos. Al dominar los conceptos de modelos, relaciones, accessors, mutators y scopes, puedes crear aplicaciones más robustas y mantenibles.
