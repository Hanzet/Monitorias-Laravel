# Optimización de Consultas en Laravel

## ¿Por qué Optimizar Consultas?

La optimización de consultas es crucial para el rendimiento de aplicaciones Laravel. Consultas mal optimizadas pueden causar:

-   Tiempos de respuesta lentos
-   Alto consumo de memoria
-   Problemas de escalabilidad
-   Experiencia de usuario deficiente

## Problemas Comunes y Soluciones

### 1. Problema N+1

**Problema**: Consultas adicionales innecesarias al cargar relaciones.

```php
// ❌ MALO - Problema N+1
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name; // Consulta adicional por cada post
}
// Resultado: 1 consulta para posts + N consultas para usuarios
```

**Solución**: Usar Eager Loading.

```php
// ✅ BUENO - Eager Loading
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name; // Sin consultas adicionales
}
// Resultado: Solo 2 consultas (posts + usuarios)
```

### 2. Carga Excesiva de Datos

**Problema**: Cargar más datos de los necesarios.

```php
// ❌ MALO - Carga excesiva
$users = User::with(['posts', 'comments', 'profile', 'settings'])->get();
// Carga todas las relaciones aunque no las uses
```

**Solución**: Cargar solo lo necesario.

```php
// ✅ BUENO - Carga selectiva
$users = User::with(['posts' => function($query) {
    $query->select('id', 'title', 'user_id');
}])->get();
// Solo carga los campos necesarios
```

### 3. Consultas sin Índices

**Problema**: Consultas que no aprovechan índices de base de datos.

```php
// ❌ MALO - Sin índices
$users = User::where('email', 'like', '%@gmail.com%')->get();
// Búsqueda lenta en tablas grandes
```

**Solución**: Usar índices apropiados.

```php
// ✅ BUENO - Con índices
// En la migración:
Schema::table('users', function (Blueprint $table) {
    $table->index('email');
});

// En la consulta:
$users = User::where('email', 'like', '%@gmail.com%')->get();
// Ahora es más rápida
```

## Técnicas de Optimización

### 1. Eager Loading Inteligente

```php
// Carga condicional de relaciones
$posts = Post::with(['user', 'comments' => function($query) {
    $query->where('approved', true);
}])->get();

// Carga de relaciones anidadas
$posts = Post::with(['user.profile', 'comments.user'])->get();

// Carga selectiva de campos
$posts = Post::with(['user:id,name,email', 'comments:id,content,post_id'])->get();
```

### 2. Consultas Selectivas

```php
// Seleccionar solo campos necesarios
$users = User::select('id', 'name', 'email')->get();

// Evitar SELECT *
$posts = Post::select('id', 'title', 'user_id', 'created_at')
    ->with(['user:id,name'])
    ->get();
```

### 3. Uso de Índices

```php
// Crear índices compuestos
Schema::table('posts', function (Blueprint $table) {
    $table->index(['user_id', 'status']);
    $table->index(['created_at', 'status']);
});

// Usar índices en consultas
$posts = Post::where('user_id', $userId)
    ->where('status', 'published')
    ->orderBy('created_at', 'desc')
    ->get();
```

### 4. Paginación Eficiente

```php
// Paginación simple
$posts = Post::paginate(15);

// Paginación con eager loading
$posts = Post::with('user')->paginate(15);

// Paginación con filtros
$posts = Post::where('status', 'published')
    ->with('user')
    ->paginate(15);
```

### 5. Consultas con Agregados

```php
// Usar agregados en lugar de cargar todos los registros
$userCount = User::count();
$totalPosts = Post::sum('views');
$avgRating = Review::avg('rating');

// Agregados con group by
$postsByUser = Post::select('user_id', DB::raw('count(*) as post_count'))
    ->groupBy('user_id')
    ->get();
```

### 6. Consultas con Subconsultas

```php
// Subconsultas eficientes
$users = User::whereHas('posts', function($query) {
    $query->where('status', 'published');
})->get();

// Subconsultas con agregados
$users = User::withCount(['posts' => function($query) {
    $query->where('status', 'published');
}])->get();
```

## Optimización de Relaciones

### 1. Relaciones Uno a Muchos

```php
// Modelo User
class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// Uso optimizado
$users = User::with(['posts' => function($query) {
    $query->select('id', 'title', 'user_id', 'created_at')
          ->where('status', 'published')
          ->orderBy('created_at', 'desc');
}])->get();
```

### 2. Relaciones Muchos a Muchos

```php
// Modelo Post
class Post extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}

// Uso optimizado
$posts = Post::with(['tags:id,name'])->get();
```

### 3. Relaciones Polimórficas

```php
// Modelo Comment
class Comment extends Model
{
    public function commentable()
    {
        return $this->morphTo();
    }
}

// Uso optimizado
$comments = Comment::with('commentable')->get();
```

## Optimización de Consultas Complejas

### 1. Consultas con Múltiples Joins

```php
// Consulta optimizada con joins
$posts = Post::select('posts.*', 'users.name as author_name', 'categories.name as category_name')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->join('categories', 'posts.category_id', '=', 'categories.id')
    ->where('posts.status', 'published')
    ->orderBy('posts.created_at', 'desc')
    ->get();
```

### 2. Consultas con Condiciones Complejas

```php
// Consulta con condiciones complejas
$posts = Post::where(function($query) {
    $query->where('status', 'published')
          ->orWhere('user_id', auth()->id());
})
->where('created_at', '>=', now()->subDays(30))
->with(['user', 'comments'])
->get();
```

### 3. Consultas con Agregados y Filtros

```php
// Consulta con agregados y filtros
$stats = Post::select(
    'user_id',
    DB::raw('count(*) as total_posts'),
    DB::raw('sum(views) as total_views'),
    DB::raw('avg(rating) as avg_rating')
)
->where('status', 'published')
->where('created_at', '>=', now()->subMonth())
->groupBy('user_id')
->having('total_posts', '>', 5)
->orderBy('total_views', 'desc')
->get();
```

## Optimización de Cache

### 1. Cache de Consultas

```php
// Cache de consultas frecuentes
$users = Cache::remember('active_users', 3600, function () {
    return User::where('status', 'active')->get();
});

// Cache con tags
$posts = Cache::tags(['posts', 'user_' . $userId])->remember(
    "user_posts_{$userId}",
    1800,
    function () use ($userId) {
        return Post::where('user_id', $userId)->get();
    }
);
```

### 2. Cache de Modelos

```php
// Cache de modelos individuales
$user = Cache::remember("user_{$id}", 3600, function () use ($id) {
    return User::find($id);
});

// Cache de relaciones
$userPosts = Cache::remember("user_{$id}_posts", 1800, function () use ($id) {
    return User::find($id)->posts()->get();
});
```

## Monitoreo y Debugging

### 1. Habilitar Query Log

```php
// En AppServiceProvider
public function boot()
{
    if (config('app.debug')) {
        DB::listen(function ($query) {
            Log::info(
                $query->sql,
                [
                    'bindings' => $query->bindings,
                    'time' => $query->time
                ]
            );
        });
    }
}
```

### 2. Usar Telescope para Debugging

```php
// En config/telescope.php
'enabled' => env('TELESCOPE_ENABLED', true),

// Ver consultas en /telescope/queries
```

### 3. Análisis de Rendimiento

```php
// Medir tiempo de consultas
$start = microtime(true);
$users = User::with('posts')->get();
$time = microtime(true) - $start;

Log::info("Query time: {$time} seconds");
```

## Mejores Prácticas

### 1. Usar Eager Loading Siempre que Sea Posible

```php
// ✅ BUENO
$posts = Post::with('user')->get();

// ❌ MALO
$posts = Post::all();
foreach ($posts as $post) {
    $post->user; // N+1 problem
}
```

### 2. Seleccionar Solo Campos Necesarios

```php
// ✅ BUENO
$users = User::select('id', 'name', 'email')->get();

// ❌ MALO
$users = User::all(); // Carga todos los campos
```

### 3. Usar Índices Apropiados

```php
// ✅ BUENO - Crear índices
Schema::table('posts', function (Blueprint $table) {
    $table->index(['user_id', 'status']);
});

// ❌ MALO - Sin índices
// Consultas lentas en tablas grandes
```

### 4. Evitar Consultas en Loops

```php
// ✅ BUENO
$users = User::with('posts')->get();
foreach ($users as $user) {
    foreach ($user->posts as $post) {
        // Procesar post
    }
}

// ❌ MALO
$users = User::all();
foreach ($users as $user) {
    $posts = $user->posts; // Consulta adicional
}
```

### 5. Usar Paginación para Grandes Conjuntos de Datos

```php
// ✅ BUENO
$posts = Post::paginate(15);

// ❌ MALO
$posts = Post::all(); // Carga todos los registros
```

### 6. Cache de Consultas Frecuentes

```php
// ✅ BUENO
$popularPosts = Cache::remember('popular_posts', 3600, function () {
    return Post::orderBy('views', 'desc')->limit(10)->get();
});

// ❌ MALO
$popularPosts = Post::orderBy('views', 'desc')->limit(10)->get(); // Sin cache
```

## Herramientas de Optimización

### 1. Laravel Debugbar

```bash
composer require barryvdh/laravel-debugbar --dev
```

### 2. Laravel Telescope

```bash
composer require laravel/telescope --dev
php artisan telescope:install
```

### 3. Query Monitor

```php
// En AppServiceProvider
DB::listen(function ($query) {
    if ($query->time > 100) { // Más de 100ms
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time
        ]);
    }
});
```

## Casos de Uso Comunes

### 1. Dashboard con Estadísticas

```php
// Consulta optimizada para dashboard
$stats = Cache::remember('dashboard_stats', 1800, function () {
    return [
        'total_users' => User::count(),
        'total_posts' => Post::count(),
        'active_users' => User::where('last_login', '>=', now()->subDays(30))->count(),
        'popular_posts' => Post::select('id', 'title', 'views')
            ->orderBy('views', 'desc')
            ->limit(5)
            ->get()
    ];
});
```

### 2. Lista de Usuarios con Posts

```php
// Consulta optimizada para lista
$users = User::withCount(['posts' => function($query) {
    $query->where('status', 'published');
}])
->with(['posts' => function($query) {
    $query->select('id', 'title', 'user_id', 'created_at')
          ->where('status', 'published')
          ->orderBy('created_at', 'desc')
          ->limit(3);
}])
->paginate(20);
```

### 3. Búsqueda Avanzada

```php
// Búsqueda optimizada
$posts = Post::with(['user:id,name', 'category:id,name'])
    ->when($request->search, function($query, $search) {
        $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    })
    ->when($request->category, function($query, $category) {
        $query->where('category_id', $category);
    })
    ->when($request->user, function($query, $user) {
        $query->where('user_id', $user);
    })
    ->where('status', 'published')
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

La optimización de consultas es un proceso continuo que requiere monitoreo constante y ajustes basados en el uso real de la aplicación.
