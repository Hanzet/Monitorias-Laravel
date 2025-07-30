# 6. Expresiones Sin Procesar

## Introducción

Las expresiones sin procesar (raw expressions) permiten usar SQL nativo directamente en las consultas de Laravel. Esto es útil cuando necesitas funcionalidades específicas de la base de datos que no están disponibles en el Query Builder.

## DB::raw() - Expresiones SQL Crudas

### Expresiones Básicas

```php
// Query Builder - expresión básica
$users = DB::table('users')
    ->select(DB::raw('id, name, email, DATE_FORMAT(created_at, "%Y-%m-%d") as created_date'))
    ->get();

// Eloquent - expresión básica
$users = User::select(DB::raw('id, name, email, DATE_FORMAT(created_at, "%Y-%m-%d") as created_date'))
    ->get();
```

### Expresiones con Parámetros

```php
// Query Builder - con parámetros
$users = DB::table('users')
    ->select(DB::raw('id, name, email, DATE_FORMAT(created_at, ?) as created_date', ['%Y-%m-%d']))
    ->get();

// Eloquent - con parámetros
$users = User::select(DB::raw('id, name, email, DATE_FORMAT(created_at, ?) as created_date', ['%Y-%m-%d']))
    ->get();
```

## Funciones de Base de Datos

### Funciones de Fecha

```php
// DATE_FORMAT
$users = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as created_date'))
    ->get();

// DATEDIFF
$users = User::select(DB::raw('DATEDIFF(NOW(), created_at) as days_since_created'))
    ->get();

// YEAR, MONTH, DAY
$users = User::select(
    DB::raw('YEAR(created_at) as year'),
    DB::raw('MONTH(created_at) as month'),
    DB::raw('DAY(created_at) as day')
)->get();

// DATE_ADD, DATE_SUB
$users = User::select(DB::raw('DATE_ADD(created_at, INTERVAL 30 DAY) as future_date'))
    ->get();
```

### Funciones de Texto

```php
// CONCAT
$users = User::select(DB::raw('CONCAT(name, " (", email, ")") as full_info'))
    ->get();

// UPPER, LOWER
$users = User::select(DB::raw('UPPER(name) as name_upper'))
    ->get();

// SUBSTRING
$users = User::select(DB::raw('SUBSTRING(name, 1, 3) as name_short'))
    ->get();

// LENGTH
$users = User::select(DB::raw('LENGTH(name) as name_length'))
    ->get();
```

### Funciones Matemáticas

```php
// ROUND
$posts = Post::select(DB::raw('ROUND(AVG(views_count), 2) as avg_views'))
    ->get();

// CEIL, FLOOR
$posts = Post::select(
    DB::raw('CEIL(views_count / 100.0) as view_groups'),
    DB::raw('FLOOR(views_count / 100.0) as view_groups_floor')
)->get();

// ABS
$comments = Comment::select(DB::raw('ABS(rating - 3) as rating_deviation'))
    ->get();
```

## Expresiones Condicionales

### CASE WHEN

```php
// CASE WHEN básico
$users = User::select(DB::raw('
    CASE
        WHEN status = "active" THEN "Activo"
        WHEN status = "inactive" THEN "Inactivo"
        ELSE "Desconocido"
    END as status_label
'))->get();

// CASE WHEN con múltiples condiciones
$posts = Post::select(DB::raw('
    CASE
        WHEN views_count > 1000 THEN "Popular"
        WHEN views_count > 500 THEN "Moderado"
        WHEN views_count > 100 THEN "Normal"
        ELSE "Bajo"
    END as popularity_level
'))->get();

// CASE WHEN con agregados
$users = User::select(DB::raw('
    CASE
        WHEN (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) > 10 THEN "Activo"
        WHEN (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) > 5 THEN "Moderado"
        ELSE "Inactivo"
    END as activity_level
'))->get();
```

### IF

```php
// IF básico
$users = User::select(DB::raw('IF(status = "active", "Activo", "Inactivo") as status_label'))
    ->get();

// IF con múltiples condiciones
$posts = Post::select(DB::raw('IF(views_count > 1000, "Popular", IF(views_count > 500, "Moderado", "Normal")) as popularity'))
    ->get();
```

## Subconsultas

### Subconsultas en SELECT

```php
// Subconsulta básica
$users = User::select(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count'))
    ->get();

// Subconsulta con condición
$users = User::select(DB::raw('
    (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id AND posts.status = "published") as published_posts
'))->get();

// Subconsulta con agregado
$users = User::select(DB::raw('
    (SELECT SUM(views_count) FROM posts WHERE posts.user_id = users.id) as total_views
'))->get();
```

### Subconsultas en WHERE

```php
// Subconsulta en WHERE
$users = User::where(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id)'), '>', 5)
    ->get();

// Subconsulta con EXISTS
$users = User::whereExists(function ($query) {
    $query->select(DB::raw(1))
          ->from('posts')
          ->whereRaw('posts.user_id = users.id')
          ->where('posts.status', 'published');
})->get();
```

## Expresiones de Agregación

### Agregados Personalizados

```php
// Agregados con condiciones
$stats = Post::select(DB::raw('
    COUNT(*) as total_posts,
    COUNT(CASE WHEN status = "published" THEN 1 END) as published_posts,
    SUM(CASE WHEN status = "published" THEN views_count ELSE 0 END) as published_views,
    AVG(CASE WHEN status = "published" THEN views_count END) as avg_published_views
'))->first();
```

### Agregados con GROUP BY

```php
// Agrupar con expresiones
$postsByMonth = Post::select(DB::raw('
    DATE_FORMAT(created_at, "%Y-%m") as month,
    COUNT(*) as post_count,
    SUM(views_count) as total_views,
    AVG(views_count) as avg_views
'))
->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
->orderBy('month')
->get();
```

## Expresiones en Joins

### Joins con Expresiones

```php
// Join con expresión
$posts = Post::join('users', DB::raw('posts.user_id = users.id AND users.status = "active"'))
    ->select('posts.*', DB::raw('users.name as author_name'))
    ->get();

// Join con subconsulta
$posts = Post::join(DB::raw('(SELECT id, name FROM users WHERE status = "active") as active_users'),
    'posts.user_id', '=', 'active_users.id')
    ->select('posts.*', DB::raw('active_users.name as author_name'))
    ->get();
```

## Expresiones en ORDER BY

### Ordenamiento con Expresiones

```php
// Ordenar por expresión
$users = User::orderBy(DB::raw('LENGTH(name)'), 'desc')
    ->get();

// Ordenar por subconsulta
$users = User::orderBy(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id)'), 'desc')
    ->get();

// Ordenar por múltiples expresiones
$posts = Post::orderBy(DB::raw('CASE WHEN status = "published" THEN 1 ELSE 0 END'), 'desc')
    ->orderBy('views_count', 'desc')
    ->get();
```

## Expresiones en HAVING

### HAVING con Expresiones

```php
// HAVING con expresión
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->having(DB::raw('COUNT(posts.id)'), '>', 5)
    ->get();

// HAVING con subconsulta
$users = User::having(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id)'), '>', 5)
    ->get();
```

## Ejemplos Prácticos

### Dashboard de Estadísticas

```php
public function getDashboardStats()
{
    return DB::table('users')
        ->select(DB::raw('
            COUNT(*) as total_users,
            COUNT(CASE WHEN status = "active" THEN 1 END) as active_users,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users,
            AVG(DATEDIFF(NOW(), created_at)) as avg_days_member
        '))
        ->first();
}
```

### Reporte de Actividad

```php
public function getActivityReport()
{
    return Post::select(DB::raw('
        DATE_FORMAT(created_at, "%Y-%m") as month,
        COUNT(*) as total_posts,
        COUNT(CASE WHEN status = "published" THEN 1 END) as published_posts,
        SUM(views_count) as total_views,
        AVG(views_count) as avg_views,
        MAX(views_count) as max_views
    '))
    ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
    ->orderBy('month')
    ->get();
}
```

### Análisis de Usuarios

```php
public function getUserAnalysis()
{
    return User::select(DB::raw('
        id,
        name,
        email,
        status,
        CASE
            WHEN status = "active" THEN "Activo"
            WHEN status = "inactive" THEN "Inactivo"
            ELSE "Desconocido"
        END as status_label,
        (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count,
        (SELECT SUM(views_count) FROM posts WHERE posts.user_id = users.id) as total_views,
        (SELECT AVG(views_count) FROM posts WHERE posts.user_id = users.id) as avg_views,
        DATEDIFF(NOW(), created_at) as days_member
    '))
    ->orderBy(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id)'), 'desc')
    ->get();
}
```

### Top Performers

```php
public function getTopPerformers()
{
    return User::select(DB::raw('
        users.*,
        (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count,
        (SELECT SUM(views_count) FROM posts WHERE posts.user_id = users.id) as total_views,
        (SELECT AVG(rating) FROM comments WHERE comments.user_id = users.id) as avg_rating
    '))
    ->having(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id)'), '>', 0)
    ->orderBy(DB::raw('(SELECT SUM(views_count) FROM posts WHERE posts.user_id = users.id)'), 'desc')
    ->limit(10)
    ->get();
}
```

## Optimización de Expresiones

### Índices para Expresiones

```sql
-- Índices para expresiones comunes
CREATE INDEX idx_users_status_created ON users(status, created_at);
CREATE INDEX idx_posts_status_views ON posts(status, views_count);
CREATE INDEX idx_posts_created_status ON posts(created_at, status);
```

### Evitar Expresiones Costosas

```php
// ❌ Evitar expresiones costosas en WHERE
$users = User::where(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id)'), '>', 5)
    ->get();

// ✅ Usar joins en su lugar
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->having('post_count', '>', 5)
    ->get();
```

## Mejores Prácticas

### 1. Usar Parámetros para Valores

```php
// ✅ Usar parámetros para evitar SQL injection
$users = User::select(DB::raw('DATE_FORMAT(created_at, ?) as created_date', ['%Y-%m-%d']))
    ->get();

// ❌ Evitar concatenación directa
$format = '%Y-%m-%d';
$users = User::select(DB::raw("DATE_FORMAT(created_at, '$format') as created_date"))
    ->get();
```

### 2. Validar Expresiones

```php
// ✅ Validar expresiones antes de usar
$allowedColumns = ['name', 'email', 'status'];
$column = $request->get('sort_by');

if (!in_array($column, $allowedColumns)) {
    $column = 'created_at';
}

$users = User::orderBy($column, 'desc')->get();
```

### 3. Usar Expresiones Solo Cuando sea Necesario

```php
// ✅ Usar Query Builder cuando sea posible
$users = User::where('status', 'active')->get();

// ✅ Usar raw solo para funcionalidades específicas
$users = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as created_date'))
    ->get();
```

### 4. Documentar Expresiones Complejas

```php
// ✅ Documentar expresiones complejas
$users = User::select(DB::raw('
    -- Calcular nivel de actividad basado en posts y comentarios
    CASE
        WHEN (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) > 10 THEN "Muy Activo"
        WHEN (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) > 5 THEN "Activo"
        ELSE "Inactivo"
    END as activity_level
'))->get();
```

## Debugging de Expresiones

### Ver SQL Generado

```php
// Ver SQL con expresiones raw
$query = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as created_date'));
dd($query->toSql()); // Muestra SQL sin ejecutar

$result = $query->get();
dd($result); // Muestra resultados
```

### Validar Expresiones

```php
// Validar expresión antes de ejecutar
try {
    $users = User::select(DB::raw('INVALID_FUNCTION(created_at) as test'))
        ->get();
} catch (Exception $e) {
    Log::error('Error en expresión SQL: ' . $e->getMessage());
}
```

---

**Próximo**: [Cláusula Inner Join](./07-clausula-inner-join.md)
