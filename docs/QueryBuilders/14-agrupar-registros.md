# Agrupar Registros en Laravel

La agrupación de registros en Laravel se realiza mediante la cláusula `groupBy`, que permite agrupar resultados basándose en valores de columnas específicas, comúnmente usado con funciones de agregación.

## Conceptos Básicos

### 1. GroupBy Simple

```php
// Agrupar usuarios por rol
$usersByRole = User::select('role', DB::raw('COUNT(*) as total'))
    ->groupBy('role')
    ->get();

// Agrupar posts por categoría
$postsByCategory = Post::select('category', DB::raw('COUNT(*) as total'))
    ->groupBy('category')
    ->get();

// Agrupar comentarios por usuario
$commentsByUser = Comment::select('user_id', DB::raw('COUNT(*) as total'))
    ->groupBy('user_id')
    ->get();
```

### 2. GroupBy con Múltiples Columnas

```php
// Agrupar posts por categoría y estado
$postsByCategoryAndStatus = Post::select('category', 'status', DB::raw('COUNT(*) as total'))
    ->groupBy('category', 'status')
    ->get();

// Agrupar usuarios por rol y estado
$usersByRoleAndStatus = User::select('role', 'status', DB::raw('COUNT(*) as total'))
    ->groupBy('role', 'status')
    ->get();
```

## GroupBy con Agregados

### 1. GroupBy con Count

```php
// Contar posts por categoría
$postCounts = Post::select('category', DB::raw('COUNT(*) as post_count'))
    ->groupBy('category')
    ->get();

// Contar comentarios por post
$commentCounts = Comment::select('post_id', DB::raw('COUNT(*) as comment_count'))
    ->groupBy('post_id')
    ->get();

// Contar usuarios por rol
$userCounts = User::select('role', DB::raw('COUNT(*) as user_count'))
    ->groupBy('role')
    ->get();
```

### 2. GroupBy con Sum

```php
// Sumar vistas por categoría de post
$viewsByCategory = Post::select('category', DB::raw('SUM(views_count) as total_views'))
    ->groupBy('category')
    ->get();

// Sumar likes por usuario
$likesByUser = Post::select('user_id', DB::raw('SUM(likes_count) as total_likes'))
    ->groupBy('user_id')
    ->get();

// Sumar comentarios por post
$commentsByPost = Comment::select('post_id', DB::raw('COUNT(*) as total_comments'))
    ->groupBy('post_id')
    ->get();
```

### 3. GroupBy con Avg

```php
// Promedio de vistas por categoría
$avgViewsByCategory = Post::select('category', DB::raw('AVG(views_count) as avg_views'))
    ->groupBy('category')
    ->get();

// Promedio de comentarios por post
$avgCommentsByPost = Post::select('user_id', DB::raw('AVG(comments_count) as avg_comments'))
    ->groupBy('user_id')
    ->get();
```

### 4. GroupBy con Max y Min

```php
// Máximo de vistas por categoría
$maxViewsByCategory = Post::select('category', DB::raw('MAX(views_count) as max_views'))
    ->groupBy('category')
    ->get();

// Mínimo de comentarios por post
$minCommentsByPost = Post::select('user_id', DB::raw('MIN(comments_count) as min_comments'))
    ->groupBy('user_id')
    ->get();
```

## GroupBy con Joins

### 1. GroupBy con Join Simple

```php
// Agrupar posts por autor
$postsByAuthor = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->select('users.name as author_name', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id', 'users.name')
    ->get();

// Agrupar comentarios por autor del post
$commentsByPostAuthor = Comment::join('posts', 'comments.post_id', '=', 'posts.id')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select('users.name as post_author', DB::raw('COUNT(comments.id) as comment_count'))
    ->groupBy('users.id', 'users.name')
    ->get();
```

### 2. GroupBy con Múltiples Joins

```php
// Agrupar comentarios por categoría de post y usuario
$commentsByCategoryAndUser = Comment::join('posts', 'comments.post_id', '=', 'posts.id')
    ->join('users', 'comments.user_id', '=', 'users.id')
    ->select('posts.category', 'users.name as user_name', DB::raw('COUNT(comments.id) as comment_count'))
    ->groupBy('posts.category', 'users.id', 'users.name')
    ->get();
```

## GroupBy con Having

### 1. Having con Condiciones

```php
// Categorías con más de 10 posts
$popularCategories = Post::select('category', DB::raw('COUNT(*) as post_count'))
    ->groupBy('category')
    ->having('post_count', '>', 10)
    ->get();

// Usuarios con más de 5 posts
$activeUsers = Post::select('user_id', DB::raw('COUNT(*) as post_count'))
    ->groupBy('user_id')
    ->having('post_count', '>', 5)
    ->get();

// Posts con más de 50 comentarios
$popularPosts = Comment::select('post_id', DB::raw('COUNT(*) as comment_count'))
    ->groupBy('post_id')
    ->having('comment_count', '>', 50)
    ->get();
```

### 2. Having con Múltiples Condiciones

```php
// Categorías con más de 10 posts y promedio de vistas mayor a 100
$topCategories = Post::select('category',
        DB::raw('COUNT(*) as post_count'),
        DB::raw('AVG(views_count) as avg_views')
    )
    ->groupBy('category')
    ->having('post_count', '>', 10)
    ->having('avg_views', '>', 100)
    ->get();
```

## GroupBy con Expresiones Raw

### 1. GroupBy con Funciones SQL

```php
// Agrupar posts por año de creación
$postsByYear = Post::select(DB::raw('YEAR(created_at) as year'), DB::raw('COUNT(*) as total'))
    ->groupBy(DB::raw('YEAR(created_at)'))
    ->get();

// Agrupar usuarios por mes de registro
$usersByMonth = User::select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as total'))
    ->groupBy(DB::raw('MONTH(created_at)'))
    ->get();

// Agrupar comentarios por día de la semana
$commentsByDay = Comment::select(DB::raw('DAYOFWEEK(created_at) as day'), DB::raw('COUNT(*) as total'))
    ->groupBy(DB::raw('DAYOFWEEK(created_at)'))
    ->get();
```

### 2. GroupBy con Expresiones Complejas

```php
// Agrupar posts por rango de vistas
$postsByViewRange = Post::select(
        DB::raw('CASE
            WHEN views_count < 100 THEN "Bajo"
            WHEN views_count < 1000 THEN "Medio"
            ELSE "Alto"
        END as view_range'),
        DB::raw('COUNT(*) as total')
    )
    ->groupBy('view_range')
    ->get();

// Agrupar usuarios por rango de edad
$usersByAgeRange = User::select(
        DB::raw('CASE
            WHEN age < 18 THEN "Menor de edad"
            WHEN age < 30 THEN "Joven"
            WHEN age < 50 THEN "Adulto"
            ELSE "Mayor"
        END as age_range'),
        DB::raw('COUNT(*) as total')
    )
    ->groupBy('age_range')
    ->get();
```

## GroupBy con Relaciones

### 1. GroupBy con WhereHas

```php
// Usuarios agrupados por cantidad de posts
$usersByPostCount = User::withCount('posts')
    ->groupBy('id')
    ->having('posts_count', '>', 0)
    ->get();

// Posts agrupados por cantidad de comentarios
$postsByCommentCount = Post::withCount('comments')
    ->groupBy('id')
    ->having('comments_count', '>', 0)
    ->get();
```

### 2. GroupBy con Relaciones Anidadas

```php
// Usuarios agrupados por posts con comentarios
$usersByPostsWithComments = User::withCount(['posts' => function($query) {
    $query->whereHas('comments');
}])
->groupBy('id')
->having('posts_count', '>', 0)
->get();
```

## Casos de Uso Avanzados

### 1. Reportes de Actividad

```php
// Reporte de actividad por día
public function getActivityReport()
{
    return DB::table('posts')
        ->select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as posts_created'),
            DB::raw('SUM(views_count) as total_views')
        )
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('date', 'desc')
        ->get();
}
```

### 2. Estadísticas de Usuarios

```php
// Estadísticas de usuarios por rol
public function getUserStats()
{
    return User::select('role',
        DB::raw('COUNT(*) as total_users'),
        DB::raw('COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users'),
        DB::raw('AVG(CASE WHEN last_login_at IS NOT NULL THEN DATEDIFF(NOW(), last_login_at) END) as avg_days_since_login')
    )
    ->groupBy('role')
    ->get();
}
```

### 3. Análisis de Contenido

```php
// Análisis de posts por categoría
public function getPostAnalysis()
{
    return Post::select('category',
        DB::raw('COUNT(*) as total_posts'),
        DB::raw('AVG(views_count) as avg_views'),
        DB::raw('AVG(comments_count) as avg_comments'),
        DB::raw('SUM(likes_count) as total_likes'),
        DB::raw('MAX(created_at) as last_post_date')
    )
    ->groupBy('category')
    ->having('total_posts', '>', 0)
    ->orderBy('total_posts', 'desc')
    ->get();
}
```

## Mejores Prácticas

### 1. Optimización de Consultas

```php
// Usar índices en columnas de agrupación
// Evitar agrupación en columnas sin índices para grandes conjuntos de datos

// Consulta optimizada
$usersByRole = User::select('role', DB::raw('COUNT(*) as total'))
    ->where('status', 'active')
    ->groupBy('role')
    ->get();
```

### 2. Validación de Datos

```php
// Validar parámetros antes de usar en agrupaciones
public function getGroupedData(Request $request)
{
    $groupBy = $request->get('group_by', 'category');
    $allowedGroups = ['category', 'status', 'user_id'];

    if (!in_array($groupBy, $allowedGroups)) {
        $groupBy = 'category';
    }

    return Post::select($groupBy, DB::raw('COUNT(*) as total'))
        ->groupBy($groupBy)
        ->get();
}
```

### 3. Manejo de Resultados Vacíos

```php
// Manejar casos donde no hay datos para agrupar
$results = Post::select('category', DB::raw('COUNT(*) as total'))
    ->groupBy('category')
    ->get();

if ($results->isEmpty()) {
    // Manejar caso sin datos
    return collect();
}
```

## Ejercicios Prácticos

### Ejercicio 1: Estadísticas Básicas

```php
// Crear estadísticas de posts por categoría que incluyan:
// - Total de posts
// - Promedio de vistas
// - Total de comentarios
// - Solo categorías con más de 5 posts

$postStats = Post::select('category',
    DB::raw('COUNT(*) as total_posts'),
    DB::raw('AVG(views_count) as avg_views'),
    DB::raw('SUM(comments_count) as total_comments')
)
->groupBy('category')
->having('total_posts', '>', 5)
->orderBy('total_posts', 'desc')
->get();
```

### Ejercicio 2: Análisis de Usuarios

```php
// Crear análisis de usuarios que incluya:
// - Usuarios por rol
// - Promedio de posts por usuario
// - Usuarios verificados vs no verificados
// - Solo roles con más de 3 usuarios

$userAnalysis = User::select('role',
    DB::raw('COUNT(*) as total_users'),
    DB::raw('COUNT(CASE WHEN email_verified_at IS NOT NULL THEN 1 END) as verified_users'),
    DB::raw('COUNT(CASE WHEN email_verified_at IS NULL THEN 1 END) as unverified_users')
)
->withCount('posts')
->groupBy('role')
->having('total_users', '>', 3)
->get();
```

### Ejercicio 3: Reporte de Actividad Temporal

```php
// Crear reporte de actividad por mes que incluya:
// - Posts creados por mes
// - Comentarios creados por mes
// - Usuarios registrados por mes
// - Solo últimos 12 meses

$monthlyActivity = DB::table('posts')
    ->select(
        DB::raw('YEAR(created_at) as year'),
        DB::raw('MONTH(created_at) as month'),
        DB::raw('COUNT(*) as posts_created')
    )
    ->where('created_at', '>=', now()->subMonths(12))
    ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
    ->orderBy('year', 'desc')
    ->orderBy('month', 'desc')
    ->get();
```

## Resumen

La agrupación en Laravel proporciona:

-   **Análisis de Datos**: Agrupar y analizar información de manera estructurada
-   **Reportes**: Generar estadísticas y reportes complejos
-   **Optimización**: Reducir consultas múltiples con agregados
-   **Flexibilidad**: Combinar con joins, having y expresiones raw
-   **Escalabilidad**: Manejar grandes conjuntos de datos eficientemente

Recuerda siempre optimizar las consultas con índices apropiados y validar los datos antes de agrupar.
