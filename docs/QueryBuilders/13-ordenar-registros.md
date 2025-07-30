# Ordenar Registros en Laravel

La ordenación de registros en Laravel se realiza mediante la cláusula `orderBy`, que permite organizar los resultados de las consultas de manera ascendente o descendente.

## Conceptos Básicos

### 1. OrderBy Simple

```php
// Ordenar usuarios por nombre ascendente
$users = User::orderBy('name', 'asc')->get();

// Ordenar posts por fecha de creación descendente
$posts = Post::orderBy('created_at', 'desc')->get();

// Ordenar comentarios por ID ascendente
$comments = Comment::orderBy('id', 'asc')->get();
```

### 2. OrderBy con Valores por Defecto

```php
// Si no se especifica dirección, por defecto es 'asc'
$users = User::orderBy('name')->get(); // Equivale a orderBy('name', 'asc')

// Ordenar por múltiples columnas
$posts = Post::orderBy('category', 'asc')
    ->orderBy('created_at', 'desc')
    ->get();
```

## Ordenación Múltiple

### 1. Múltiples Columnas

```php
// Ordenar usuarios por rol y luego por nombre
$users = User::orderBy('role', 'asc')
    ->orderBy('name', 'asc')
    ->get();

// Ordenar posts por estado, categoría y fecha
$posts = Post::orderBy('status', 'asc')
    ->orderBy('category', 'asc')
    ->orderBy('created_at', 'desc')
    ->get();
```

### 2. Ordenación con Relaciones

```php
// Ordenar posts por nombre del autor
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->orderBy('users.name', 'asc')
    ->select('posts.*', 'users.name as author_name')
    ->get();

// Ordenar comentarios por nombre del usuario y fecha
$comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
    ->orderBy('users.name', 'asc')
    ->orderBy('comments.created_at', 'desc')
    ->select('comments.*', 'users.name as user_name')
    ->get();
```

## Ordenación con Agregados

### 1. OrderBy con Count

```php
// Ordenar usuarios por cantidad de posts
$users = User::withCount('posts')
    ->orderBy('posts_count', 'desc')
    ->get();

// Ordenar posts por cantidad de comentarios
$posts = Post::withCount('comments')
    ->orderBy('comments_count', 'desc')
    ->get();
```

### 2. OrderBy con Sum

```php
// Ordenar posts por total de vistas
$posts = Post::withSum('views', 'views_count')
    ->orderBy('views_sum', 'desc')
    ->get();

// Ordenar usuarios por total de likes en sus posts
$users = User::withSum('posts', 'likes_count')
    ->orderBy('posts_sum_likes_count', 'desc')
    ->get();
```

## Ordenación con Expresiones Raw

### 1. OrderByRaw

```php
// Ordenar usuarios por longitud del nombre
$users = User::orderByRaw('LENGTH(name) DESC')->get();

// Ordenar posts por fecha formateada
$posts = Post::orderByRaw('DATE_FORMAT(created_at, "%Y-%m-%d") DESC')->get();

// Ordenar comentarios por distancia de fecha desde hoy
$comments = Comment::orderByRaw('ABS(DATEDIFF(created_at, NOW())) ASC')->get();
```

### 2. OrderBy con Funciones SQL

```php
// Ordenar usuarios por nombre en minúsculas
$users = User::orderByRaw('LOWER(name) ASC')->get();

// Ordenar posts por palabras en el título
$posts = Post::orderByRaw('LENGTH(title) - LENGTH(REPLACE(title, " ", "")) + 1 DESC')->get();

// Ordenar comentarios por día de la semana
$comments = Comment::orderByRaw('DAYOFWEEK(created_at) ASC')->get();
```

## Ordenación Condicional

### 1. OrderBy con When

```php
// Ordenación condicional basada en parámetros
$sortBy = $request->sort_by ?? 'created_at';
$sortOrder = $request->sort_order ?? 'desc';

$posts = Post::query()
    ->when($sortBy === 'title', function($query) use ($sortOrder) {
        return $query->orderBy('title', $sortOrder);
    })
    ->when($sortBy === 'views', function($query) use ($sortOrder) {
        return $query->orderBy('views_count', $sortOrder);
    })
    ->when($sortBy === 'comments', function($query) use ($sortOrder) {
        return $query->withCount('comments')->orderBy('comments_count', $sortOrder);
    })
    ->when($sortBy === 'created_at', function($query) use ($sortOrder) {
        return $query->orderBy('created_at', $sortOrder);
    })
    ->get();
```

### 2. OrderBy con Arrays

```php
// Ordenación basada en arrays de valores
$priorityCategories = ['featured', 'tech', 'science', 'health'];

$posts = Post::orderByRaw("FIELD(category, '" . implode("','", $priorityCategories) . "') DESC")
    ->orderBy('created_at', 'desc')
    ->get();
```

## Ordenación con Subconsultas

### 1. OrderBy con Subconsultas

```php
// Ordenar usuarios por su último post
$users = User::orderBy(function($query) {
    $query->select('created_at')
        ->from('posts')
        ->whereColumn('posts.user_id', 'users.id')
        ->orderBy('created_at', 'desc')
        ->limit(1);
}, 'desc')->get();

// Ordenar posts por comentario más reciente
$posts = Post::orderBy(function($query) {
    $query->select('created_at')
        ->from('comments')
        ->whereColumn('comments.post_id', 'posts.id')
        ->orderBy('created_at', 'desc')
        ->limit(1);
}, 'desc')->get();
```

## Ordenación con Relaciones Eager Loading

### 1. OrderBy con With

```php
// Ordenar usuarios y cargar sus posts ordenados
$users = User::with(['posts' => function($query) {
    $query->orderBy('created_at', 'desc');
}])
->orderBy('name', 'asc')
->get();

// Ordenar posts y cargar comentarios ordenados
$posts = Post::with(['comments' => function($query) {
    $query->orderBy('created_at', 'desc');
}])
->orderBy('created_at', 'desc')
->get();
```

### 2. OrderBy con Relaciones Anidadas

```php
// Ordenar posts y cargar comentarios con usuarios ordenados
$posts = Post::with(['comments.user' => function($query) {
    $query->orderBy('name', 'asc');
}])
->orderBy('created_at', 'desc')
->get();
```

## Casos de Uso Avanzados

### 1. Sistema de Ordenación Dinámico

```php
// Sistema completo de ordenación
public function getPosts(Request $request)
{
    $query = Post::where('status', 'published');

    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');

    switch ($sortBy) {
        case 'title':
            $query->orderBy('title', $sortOrder);
            break;
        case 'author':
            $query->join('users', 'posts.user_id', '=', 'users.id')
                ->orderBy('users.name', $sortOrder)
                ->select('posts.*', 'users.name as author_name');
            break;
        case 'views':
            $query->orderBy('views_count', $sortOrder);
            break;
        case 'comments':
            $query->withCount('comments')
                ->orderBy('comments_count', $sortOrder);
            break;
        case 'likes':
            $query->orderBy('likes_count', $sortOrder);
            break;
        case 'created_at':
        default:
            $query->orderBy('created_at', $sortOrder);
            break;
    }

    return $query->get();
}
```

### 2. Ordenación con Filtros

```php
// Ordenación con filtros aplicados
public function filterAndSortUsers(Request $request)
{
    $query = User::query();

    // Aplicar filtros
    if ($request->status) {
        $query->where('status', $request->status);
    }

    if ($request->role) {
        $query->where('role', $request->role);
    }

    if ($request->search) {
        $query->where('name', 'like', "%{$request->search}%");
    }

    // Aplicar ordenación
    $sortBy = $request->get('sort_by', 'name');
    $sortOrder = $request->get('sort_order', 'asc');

    switch ($sortBy) {
        case 'posts_count':
            $query->withCount('posts')->orderBy('posts_count', $sortOrder);
            break;
        case 'comments_count':
            $query->withCount('comments')->orderBy('comments_count', $sortOrder);
            break;
        case 'last_login':
            $query->orderBy('last_login_at', $sortOrder);
            break;
        case 'name':
        default:
            $query->orderBy('name', $sortOrder);
            break;
    }

    return $query->get();
}
```

### 3. Ordenación con Paginación

```php
// Ordenación con paginación
public function getPaginatedPosts(Request $request)
{
    $perPage = $request->get('per_page', 15);
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');

    return Post::where('status', 'published')
        ->when($sortBy === 'comments', function($query) use ($sortOrder) {
            return $query->withCount('comments')->orderBy('comments_count', $sortOrder);
        })
        ->when($sortBy === 'views', function($query) use ($sortOrder) {
            return $query->orderBy('views_count', $sortOrder);
        })
        ->when($sortBy === 'created_at', function($query) use ($sortOrder) {
            return $query->orderBy('created_at', $sortOrder);
        })
        ->with('user')
        ->paginate($perPage);
}
```

## Mejores Prácticas

### 1. Optimización de Consultas

```php
// Usar índices en columnas de ordenación
// Evitar ordenación en columnas sin índices para grandes conjuntos de datos

// Consulta optimizada
$users = User::select('id', 'name', 'email', 'created_at')
    ->orderBy('created_at', 'desc')
    ->get();
```

### 2. Validación de Parámetros

```php
// Validar parámetros de ordenación
public function getUsers(Request $request)
{
    $allowedSortFields = ['name', 'email', 'created_at', 'status'];
    $allowedSortOrders = ['asc', 'desc'];

    $sortBy = in_array($request->sort_by, $allowedSortFields)
        ? $request->sort_by
        : 'created_at';

    $sortOrder = in_array($request->sort_order, $allowedSortOrders)
        ? $request->sort_order
        : 'desc';

    return User::orderBy($sortBy, $sortOrder)->get();
}
```

### 3. Ordenación por Defecto

```php
// Siempre especificar ordenación por defecto
$posts = Post::where('status', 'published')
    ->orderBy('created_at', 'desc') // Ordenación por defecto
    ->get();

// O usar latest() para ordenar por created_at desc
$posts = Post::where('status', 'published')
    ->latest()
    ->get();
```

## Ejercicios Prácticos

### Ejercicio 1: Ordenación Básica

```php
// Crear una consulta que ordene usuarios por:
// 1. Rol (ascendente)
// 2. Nombre (ascendente)
// 3. Fecha de registro (descendente)

$users = User::orderBy('role', 'asc')
    ->orderBy('name', 'asc')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Ejercicio 2: Ordenación con Agregados

```php
// Crear una consulta que ordene posts por:
// 1. Cantidad de comentarios (descendente)
// 2. Cantidad de vistas (descendente)
// 3. Fecha de creación (descendente)

$posts = Post::withCount('comments')
    ->orderBy('comments_count', 'desc')
    ->orderBy('views_count', 'desc')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Ejercicio 3: Ordenación Dinámica

```php
// Crear un sistema de ordenación que permita ordenar por:
// - Título del post
// - Nombre del autor
// - Cantidad de comentarios
// - Fecha de creación

public function getOrderedPosts(Request $request)
{
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');

    $query = Post::where('status', 'published');

    switch ($sortBy) {
        case 'title':
            $query->orderBy('title', $sortOrder);
            break;
        case 'author':
            $query->join('users', 'posts.user_id', '=', 'users.id')
                ->orderBy('users.name', $sortOrder)
                ->select('posts.*', 'users.name as author_name');
            break;
        case 'comments':
            $query->withCount('comments')
                ->orderBy('comments_count', $sortOrder);
            break;
        case 'created_at':
        default:
            $query->orderBy('created_at', $sortOrder);
            break;
    }

    return $query->get();
}
```

## Resumen

La ordenación en Laravel proporciona:

-   **Flexibilidad**: Múltiples formas de ordenar datos
-   **Rendimiento**: Optimización con índices apropiados
-   **Funcionalidad**: Ordenación simple y compleja
-   **Mantenibilidad**: Código limpio y legible
-   **Escalabilidad**: Funciona con grandes conjuntos de datos

Recuerda siempre especificar una ordenación por defecto y optimizar las consultas para mejor rendimiento.
