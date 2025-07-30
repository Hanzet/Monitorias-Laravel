# Cláusula WhereNot en Laravel

La cláusula `whereNot` en Laravel permite excluir registros que cumplan ciertas condiciones, proporcionando una forma elegante de filtrar datos negativamente.

## Conceptos Básicos

### 1. WhereNot Simple

```php
// Excluir usuarios inactivos
$users = User::whereNot('status', 'inactive')->get();

// Excluir posts borradores
$posts = Post::whereNot('status', 'draft')->get();

// Excluir comentarios eliminados
$comments = Comment::whereNot('deleted_at', null)->get();
```

### 2. WhereNot con Operadores

```php
// Excluir usuarios con edad menor a 18
$users = User::whereNot('age', '<', 18)->get();

// Excluir posts con menos de 10 vistas
$posts = Post::whereNot('views_count', '<', 10)->get();

// Excluir comentarios con contenido vacío
$comments = Comment::whereNot('content', '=', '')->get();
```

## WhereNot con Arrays

### 1. WhereNotIn

```php
// Excluir usuarios con roles específicos
$excludedRoles = ['guest', 'banned'];
$users = User::whereNotIn('role', $excludedRoles)->get();

// Excluir posts de categorías específicas
$excludedCategories = ['private', 'archived'];
$posts = Post::whereNotIn('category', $excludedCategories)->get();

// Excluir comentarios de usuarios específicos
$excludedUserIds = [1, 5, 10];
$comments = Comment::whereNotIn('user_id', $excludedUserIds)->get();
```

### 2. WhereNotBetween

```php
// Excluir usuarios registrados en un rango de fechas
$users = User::whereNotBetween('created_at', [
    '2023-01-01',
    '2023-12-31'
])->get();

// Excluir posts con vistas en un rango específico
$posts = Post::whereNotBetween('views_count', [0, 50])->get();

// Excluir comentarios creados en un período específico
$comments = Comment::whereNotBetween('created_at', [
    now()->subDays(30),
    now()
])->get();
```

## WhereNot con Relaciones

### 1. WhereNotHas

```php
// Excluir usuarios que no tienen posts
$users = User::whereNotHas('posts')->get();

// Excluir posts que no tienen comentarios
$posts = Post::whereNotHas('comments')->get();

// Excluir usuarios que no tienen comentarios recientes
$users = User::whereNotHas('comments', function($query) {
    $query->where('created_at', '>=', now()->subDays(7));
})->get();
```

### 2. WhereNotHas con Relaciones Anidadas

```php
// Excluir posts que no tienen comentarios de usuarios verificados
$posts = Post::whereNotHas('comments.user', function($query) {
    $query->where('email_verified_at', '!=', null);
})->get();

// Excluir usuarios que no tienen posts con más de 100 vistas
$users = User::whereNotHas('posts', function($query) {
    $query->where('views_count', '>', 100);
})->get();
```

## WhereNot con Agregados

### 1. WhereNot con Count

```php
// Excluir usuarios con más de 10 posts
$users = User::whereNotHas('posts', function($query) {
    $query->havingRaw('COUNT(*) > 10');
})->get();

// Excluir posts con más de 50 comentarios
$posts = Post::whereNotHas('comments', function($query) {
    $query->havingRaw('COUNT(*) > 50');
})->get();
```

### 2. WhereNot con Sum

```php
// Excluir posts con más de 1000 vistas totales
$posts = Post::whereNotHas('views', function($query) {
    $query->havingRaw('SUM(views_count) > 1000');
})->get();
```

## WhereNot con Expresiones Raw

### 1. WhereNot con SQL Raw

```php
// Excluir usuarios registrados hace más de 1 año
$users = User::whereNot(DB::raw('DATEDIFF(NOW(), created_at)'), '>', 365)->get();

// Excluir posts con título que contenga palabras específicas
$posts = Post::whereNot(DB::raw('LOWER(title)'), 'like', '%spam%')->get();

// Excluir comentarios con contenido muy corto
$comments = Comment::whereNot(DB::raw('LENGTH(content)'), '<', 10)->get();
```

### 2. WhereNot con Subconsultas

```php
// Excluir usuarios que han comentado
$users = User::whereNotExists(function($query) {
    $query->select(DB::raw(1))
        ->from('comments')
        ->whereRaw('comments.user_id = users.id');
})->get();

// Excluir posts que tienen comentarios de usuarios verificados
$posts = Post::whereNotExists(function($query) {
    $query->select(DB::raw(1))
        ->from('comments')
        ->join('users', 'comments.user_id', '=', 'users.id')
        ->whereRaw('comments.post_id = posts.id')
        ->where('users.email_verified_at', '!=', null);
})->get();
```

## WhereNot con Condiciones Dinámicas

### 1. WhereNot con When

```php
// WhereNot condicional basado en parámetros
$excludeStatus = $request->exclude_status;
$excludeCategory = $request->exclude_category;

$posts = Post::query()
    ->when($excludeStatus, function($query, $status) {
        return $query->whereNot('status', $status);
    })
    ->when($excludeCategory, function($query, $category) {
        return $query->whereNot('category', $category);
    })
    ->get();
```

### 2. WhereNot con Arrays Dinámicos

```php
// WhereNot con valores dinámicos
$excludedIds = $request->exclude_ids ?? [];

$users = User::query()
    ->when(!empty($excludedIds), function($query) use ($excludedIds) {
        return $query->whereNotIn('id', $excludedIds);
    })
    ->get();
```

## WhereNot con Joins

### 1. WhereNot en Joins

```php
// Excluir posts de usuarios inactivos
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->whereNot('users.status', 'inactive')
    ->select('posts.*', 'users.name as author_name')
    ->get();

// Excluir comentarios de posts privados
$comments = Comment::join('posts', 'comments.post_id', '=', 'posts.id')
    ->whereNot('posts.status', 'private')
    ->select('comments.*', 'posts.title as post_title')
    ->get();
```

### 2. WhereNot con Múltiples Joins

```php
// Excluir comentarios de usuarios no verificados en posts públicos
$comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
    ->join('posts', 'comments.post_id', '=', 'posts.id')
    ->whereNot('users.email_verified_at', null)
    ->whereNot('posts.status', 'private')
    ->select('comments.*', 'users.name as commenter_name', 'posts.title as post_title')
    ->get();
```

## Casos de Uso Comunes

### 1. Filtros de Exclusión

```php
// Excluir contenido inapropiado
$posts = Post::whereNotIn('status', ['spam', 'inappropriate', 'deleted'])
    ->whereNot('title', 'like', '%spam%')
    ->get();

// Excluir usuarios con problemas
$users = User::whereNotIn('status', ['banned', 'suspended'])
    ->whereNot('email_verified_at', null)
    ->get();
```

### 2. Limpieza de Datos

```php
// Excluir registros duplicados o vacíos
$users = User::whereNot('email', '')
    ->whereNot('name', '')
    ->whereNot('email', null)
    ->get();

// Excluir posts sin contenido
$posts = Post::whereNot('content', '')
    ->whereNot('content', null)
    ->whereNot(DB::raw('LENGTH(content)'), '<', 50)
    ->get();
```

### 3. Reportes Excluyentes

```php
// Usuarios activos excluyendo administradores
$activeUsers = User::where('status', 'active')
    ->whereNot('role', 'admin')
    ->whereNot('is_admin', true)
    ->get();

// Posts populares excluyendo destacados
$popularPosts = Post::where('views_count', '>', 100)
    ->whereNot('is_featured', true)
    ->whereNot('status', 'draft')
    ->get();
```

## Mejores Prácticas

### 1. Combinación con Where

```php
// Combinar where y whereNot para filtros complejos
$users = User::where('status', 'active')
    ->whereNot('role', 'guest')
    ->whereNot('email_verified_at', null)
    ->get();
```

### 2. Optimización de Consultas

```php
// Usar índices apropiados para whereNot
// Evitar whereNot en columnas sin índices

// Consulta optimizada
$posts = Post::where('status', 'published')
    ->whereNotIn('category', ['private', 'archived'])
    ->select('id', 'title', 'content', 'views_count')
    ->get();
```

### 3. Validación de Datos

```php
// Validar parámetros antes de usar en whereNot
$excludeIds = $request->get('exclude_ids', []);
$excludeIds = array_filter($excludeIds, 'is_numeric');

if (!empty($excludeIds)) {
    $users = User::whereNotIn('id', $excludeIds)->get();
} else {
    $users = User::all();
}
```

## Ejercicios Prácticos

### Ejercicio 1: Filtros de Exclusión

```php
// Crear un filtro que excluya:
// - Usuarios inactivos
// - Usuarios no verificados
// - Usuarios con rol 'guest'
// - Usuarios registrados hace más de 1 año

$filteredUsers = User::whereNot('status', 'inactive')
    ->whereNot('email_verified_at', null)
    ->whereNot('role', 'guest')
    ->whereNot(DB::raw('DATEDIFF(NOW(), created_at)'), '>', 365)
    ->get();
```

### Ejercicio 2: Limpieza de Contenido

```php
// Crear un filtro que excluya posts:
// - Sin contenido
// - Con título muy corto
// - De categorías privadas
// - Con menos de 5 vistas

$cleanPosts = Post::whereNot('content', '')
    ->whereNot('content', null)
    ->whereNot(DB::raw('LENGTH(title)'), '<', 10)
    ->whereNotIn('category', ['private', 'draft'])
    ->whereNot('views_count', '<', 5)
    ->get();
```

### Ejercicio 3: Reporte de Actividad

```php
// Crear un reporte de usuarios activos excluyendo:
// - Administradores
// - Usuarios sin actividad reciente
// - Usuarios sin posts

$activeUsers = User::where('status', 'active')
    ->whereNot('is_admin', true)
    ->whereNot('last_login_at', '<', now()->subDays(30))
    ->whereNotHas('posts')
    ->withCount(['posts', 'comments'])
    ->get();
```

## Resumen

La cláusula `whereNot` es esencial para crear filtros de exclusión en Laravel. Permite:

-   **Exclusión Simple**: Excluir registros con valores específicos
-   **Exclusión Múltiple**: Usar `whereNotIn` para excluir múltiples valores
-   **Exclusión de Rangos**: Usar `whereNotBetween` para excluir rangos
-   **Exclusión de Relaciones**: Usar `whereNotHas` para excluir basado en relaciones
-   **Exclusión Avanzada**: Combinar con expresiones raw y subconsultas
-   **Filtros Dinámicos**: Crear exclusiones basadas en parámetros

Recuerda siempre optimizar las consultas y validar los datos antes de usar en filtros de exclusión.
