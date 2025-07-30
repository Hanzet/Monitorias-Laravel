# Cláusula OrWhere en Laravel

La cláusula `orWhere` en Laravel permite agregar condiciones OR a las consultas, proporcionando flexibilidad para consultas más complejas.

## Conceptos Básicos

### 1. OrWhere Simple

```php
// Buscar usuarios activos O administradores
$users = User::where('status', 'active')
    ->orWhere('is_admin', true)
    ->get();

// Buscar posts con más de 100 vistas O destacados
$posts = Post::where('views_count', '>', 100)
    ->orWhere('is_featured', true)
    ->get();
```

### 2. OrWhere con Múltiples Condiciones

```php
// Buscar usuarios con email específico O nombre que contenga 'admin'
$users = User::where('email', 'admin@example.com')
    ->orWhere('name', 'like', '%admin%')
    ->get();

// Buscar posts de categoría 'tech' O con más de 50 comentarios
$posts = Post::where('category', 'tech')
    ->orWhere('comments_count', '>', 50)
    ->get();
```

## OrWhere con Relaciones

### 1. OrWhere con WhereHas

```php
// Usuarios que tienen posts O comentarios
$users = User::whereHas('posts')
    ->orWhereHas('comments')
    ->get();

// Usuarios con posts publicados O comentarios recientes
$users = User::whereHas('posts', function($query) {
        $query->where('status', 'published');
    })
    ->orWhereHas('comments', function($query) {
        $query->where('created_at', '>=', now()->subDays(7));
    })
    ->get();
```

### 2. OrWhere con Relaciones Anidadas

```php
// Posts que tienen comentarios O likes
$posts = Post::whereHas('comments')
    ->orWhereHas('likes')
    ->get();

// Posts con comentarios de usuarios verificados O más de 100 vistas
$posts = Post::whereHas('comments.user', function($query) {
        $query->where('email_verified_at', '!=', null);
    })
    ->orWhere('views_count', '>', 100)
    ->get();
```

## OrWhere con Agregados

### 1. OrWhere con Count

```php
// Usuarios con más de 5 posts O más de 10 comentarios
$users = User::whereHas('posts', function($query) {
        $query->havingRaw('COUNT(*) > 5');
    })
    ->orWhereHas('comments', function($query) {
        $query->havingRaw('COUNT(*) > 10');
    })
    ->get();
```

### 2. OrWhere con Sum

```php
// Posts con más de 1000 vistas totales O más de 50 comentarios
$posts = Post::whereHas('views', function($query) {
        $query->havingRaw('SUM(views_count) > 1000');
    })
    ->orWhere('comments_count', '>', 50)
    ->get();
```

## OrWhere con Expresiones Raw

### 1. OrWhere con SQL Raw

```php
// Usuarios con edad mayor a 25 O registrados hace más de 1 año
$users = User::where('age', '>', 25)
    ->orWhere(DB::raw('DATEDIFF(NOW(), created_at)'), '>', 365)
    ->get();

// Posts con título que contenga 'laravel' O contenido con 'php'
$posts = Post::where('title', 'like', '%laravel%')
    ->orWhere(DB::raw('LOWER(content)'), 'like', '%php%')
    ->get();
```

### 2. OrWhere con Subconsultas

```php
// Usuarios que han comentado O tienen posts con más de 100 vistas
$users = User::whereExists(function($query) {
        $query->select(DB::raw(1))
            ->from('comments')
            ->whereRaw('comments.user_id = users.id');
    })
    ->orWhereExists(function($query) {
        $query->select(DB::raw(1))
            ->from('posts')
            ->whereRaw('posts.user_id = users.id')
            ->where('views_count', '>', 100);
    })
    ->get();
```

## OrWhere con Condiciones Dinámicas

### 1. OrWhere con When

```php
// OrWhere condicional basado en parámetros
$status = $request->status;
$category = $request->category;

$posts = Post::where('status', 'published')
    ->when($status, function($query, $status) {
        return $query->orWhere('status', $status);
    })
    ->when($category, function($query, $category) {
        return $query->orWhere('category', $category);
    })
    ->get();
```

### 2. OrWhere con Arrays

```php
// OrWhere con múltiples valores
$categories = ['tech', 'science', 'health'];

$posts = Post::where('status', 'published')
    ->orWhereIn('category', $categories)
    ->get();

// OrWhere con rangos
$posts = Post::where('status', 'published')
    ->orWhereBetween('created_at', [
        now()->subDays(7),
        now()
    ])
    ->get();
```

## OrWhere con Joins

### 1. OrWhere en Joins

```php
// Posts con autor activo O con más de 50 comentarios
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->where('users.status', 'active')
    ->orWhere('posts.comments_count', '>', 50)
    ->select('posts.*', 'users.name as author_name')
    ->get();
```

### 2. OrWhere con Múltiples Joins

```php
// Comentarios de usuarios verificados O en posts destacados
$comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
    ->join('posts', 'comments.post_id', '=', 'posts.id')
    ->where('users.email_verified_at', '!=', null)
    ->orWhere('posts.is_featured', true)
    ->select('comments.*', 'users.name as commenter_name', 'posts.title as post_title')
    ->get();
```

## Casos de Uso Comunes

### 1. Búsqueda de Texto

```php
// Búsqueda en múltiples campos
$search = $request->search;

$users = User::where('name', 'like', "%{$search}%")
    ->orWhere('email', 'like', "%{$search}%")
    ->orWhere('username', 'like', "%{$search}%")
    ->get();
```

### 2. Filtros Múltiples

```php
// Filtros con opciones OR
$filters = $request->only(['status', 'category', 'author']);

$posts = Post::query();

foreach ($filters as $field => $value) {
    if ($value) {
        $posts->orWhere($field, $value);
    }
}

$posts = $posts->get();
```

### 3. Consultas de Reportes

```php
// Usuarios activos O con actividad reciente
$users = User::where('status', 'active')
    ->orWhere('last_login_at', '>=', now()->subDays(30))
    ->orWhereHas('posts', function($query) {
        $query->where('created_at', '>=', now()->subDays(7));
    })
    ->get();
```

## Mejores Prácticas

### 1. Agrupación de Condiciones

```php
// Usar paréntesis para agrupar condiciones OR
$users = User::where('status', 'active')
    ->where(function($query) {
        $query->where('age', '>', 18)
            ->orWhere('is_admin', true);
    })
    ->get();
```

### 2. Optimización de Consultas

```php
// Usar índices apropiados
// Agregar índices en las columnas más consultadas
// Evitar OR en columnas sin índices

// Consulta optimizada
$users = User::where('status', 'active')
    ->orWhere('email_verified_at', '!=', null)
    ->select('id', 'name', 'email', 'status') // Seleccionar solo columnas necesarias
    ->get();
```

### 3. Validación de Datos

```php
// Validar parámetros antes de usar en consultas
$search = $request->get('search', '');
$search = trim($search);

if (strlen($search) >= 3) {
    $users = User::where('name', 'like', "%{$search}%")
        ->orWhere('email', 'like', "%{$search}%")
        ->get();
} else {
    $users = User::all();
}
```

## Ejercicios Prácticos

### Ejercicio 1: Búsqueda Avanzada

```php
// Crear una búsqueda que encuentre usuarios que:
// - Tengan email que contenga 'admin' O
// - Sean administradores O
// - Tengan posts con más de 100 vistas

$users = User::where('email', 'like', '%admin%')
    ->orWhere('is_admin', true)
    ->orWhereHas('posts', function($query) {
        $query->where('views_count', '>', 100);
    })
    ->with('posts')
    ->get();
```

### Ejercicio 2: Filtros Dinámicos

```php
// Crear un sistema de filtros que permita buscar posts por:
// - Categoría O
// - Autor O
// - Estado O
// - Fecha de creación

public function filterPosts(Request $request)
{
    $query = Post::query();

    if ($request->category) {
        $query->orWhere('category', $request->category);
    }

    if ($request->author) {
        $query->orWhereHas('user', function($q) use ($request) {
            $q->where('name', 'like', "%{$request->author}%");
        });
    }

    if ($request->status) {
        $query->orWhere('status', $request->status);
    }

    if ($request->date_from && $request->date_to) {
        $query->orWhereBetween('created_at', [
            $request->date_from,
            $request->date_to
        ]);
    }

    return $query->with('user')->get();
}
```

### Ejercicio 3: Reporte de Actividad

```php
// Crear un reporte de usuarios activos que:
// - Se hayan registrado en los últimos 30 días O
// - Hayan hecho login en los últimos 7 días O
// - Hayan creado posts en los últimos 3 días

$activeUsers = User::where('created_at', '>=', now()->subDays(30))
    ->orWhere('last_login_at', '>=', now()->subDays(7))
    ->orWhereHas('posts', function($query) {
        $query->where('created_at', '>=', now()->subDays(3));
    })
    ->withCount(['posts', 'comments'])
    ->get();
```

## Resumen

La cláusula `orWhere` es fundamental para crear consultas flexibles y complejas en Laravel. Permite:

-   **Flexibilidad**: Combinar múltiples condiciones con lógica OR
-   **Relaciones**: Usar con `whereHas` y relaciones anidadas
-   **Agregados**: Combinar con funciones de agregación
-   **Expresiones Raw**: Usar SQL personalizado cuando sea necesario
-   **Condiciones Dinámicas**: Crear consultas basadas en parámetros

Recuerda siempre agrupar condiciones apropiadamente y optimizar las consultas para mejor rendimiento.
