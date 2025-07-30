# 4. Agregados

## Introducción

Las funciones de agregación permiten realizar cálculos sobre conjuntos de datos, como contar registros, sumar valores, calcular promedios, etc. Laravel proporciona métodos convenientes para trabajar con estas funciones SQL.

## Funciones de Agregación Básicas

### count() - Contar Registros

```php
// Query Builder - contar todos los registros
$totalUsers = DB::table('users')->count();

// Query Builder - contar con condición
$activeUsers = DB::table('users')
    ->where('status', 'active')
    ->count();

// Eloquent - contar todos los registros
$totalUsers = User::count();

// Eloquent - contar con condición
$activeUsers = User::where('status', 'active')->count();

// Eloquent - contar relaciones
$usersWithPosts = User::has('posts')->count();
```

### sum() - Sumar Valores

```php
// Query Builder - sumar una columna
$totalViews = DB::table('posts')->sum('views_count');

// Query Builder - sumar con condición
$totalViewsActive = DB::table('posts')
    ->where('status', 'published')
    ->sum('views_count');

// Eloquent - sumar una columna
$totalViews = Post::sum('views_count');

// Eloquent - sumar con condición
$totalViewsActive = Post::where('status', 'published')
    ->sum('views_count');
```

### avg() - Calcular Promedio

```php
// Query Builder - promedio de una columna
$averageRating = DB::table('comments')->avg('rating');

// Query Builder - promedio con condición
$averageRatingRecent = DB::table('comments')
    ->where('created_at', '>=', now()->subDays(30))
    ->avg('rating');

// Eloquent - promedio de una columna
$averageRating = Comment::avg('rating');

// Eloquent - promedio con condición
$averageRatingRecent = Comment::where('created_at', '>=', now()->subDays(30))
    ->avg('rating');
```

### max() - Valor Máximo

```php
// Query Builder - valor máximo
$maxViews = DB::table('posts')->max('views_count');

// Query Builder - máximo con condición
$maxViewsActive = DB::table('posts')
    ->where('status', 'published')
    ->max('views_count');

// Eloquent - valor máximo
$maxViews = Post::max('views_count');

// Eloquent - máximo con condición
$maxViewsActive = Post::where('status', 'published')
    ->max('views_count');
```

### min() - Valor Mínimo

```php
// Query Builder - valor mínimo
$minRating = DB::table('comments')->min('rating');

// Query Builder - mínimo con condición
$minRatingRecent = DB::table('comments')
    ->where('created_at', '>=', now()->subDays(30))
    ->min('rating');

// Eloquent - valor mínimo
$minRating = Comment::min('rating');

// Eloquent - mínimo con condición
$minRatingRecent = Comment::where('created_at', '>=', now()->subDays(30))
    ->min('rating');
```

## Agregados Múltiples

### selectRaw() con Múltiples Agregados

```php
// Query Builder - múltiples agregados
$stats = DB::table('posts')
    ->selectRaw('
        COUNT(*) as total_posts,
        SUM(views_count) as total_views,
        AVG(views_count) as average_views,
        MAX(views_count) as max_views,
        MIN(views_count) as min_views
    ')
    ->first();

// Eloquent - múltiples agregados
$stats = Post::selectRaw('
    COUNT(*) as total_posts,
    SUM(views_count) as total_views,
    AVG(views_count) as average_views,
    MAX(views_count) as max_views,
    MIN(views_count) as min_views
')->first();
```

### Agregados con Condiciones

```php
// Query Builder - agregados con condiciones
$stats = DB::table('posts')
    ->selectRaw('
        COUNT(*) as total_posts,
        COUNT(CASE WHEN status = "published" THEN 1 END) as published_posts,
        COUNT(CASE WHEN status = "draft" THEN 1 END) as draft_posts,
        SUM(CASE WHEN status = "published" THEN views_count ELSE 0 END) as published_views
    ')
    ->first();

// Eloquent - agregados con condiciones
$stats = Post::selectRaw('
    COUNT(*) as total_posts,
    COUNT(CASE WHEN status = "published" THEN 1 END) as published_posts,
    COUNT(CASE WHEN status = "draft" THEN 1 END) as draft_posts,
    SUM(CASE WHEN status = "published" THEN views_count ELSE 0 END) as published_views
')->first();
```

## Agregados por Grupos

### groupBy() - Agrupar por Columna

```php
// Query Builder - agrupar por estado
$postsByStatus = DB::table('posts')
    ->select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->get();

// Eloquent - agrupar por estado
$postsByStatus = Post::select('status', DB::raw('COUNT(*) as count'))
    ->groupBy('status')
    ->get();
```

### Agrupar por Múltiples Columnas

```php
// Query Builder - agrupar por usuario y estado
$postsByUserAndStatus = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select('users.name', 'posts.status', DB::raw('COUNT(*) as count'))
    ->groupBy('users.name', 'posts.status')
    ->get();

// Eloquent - agrupar por usuario y estado
$postsByUserAndStatus = Post::with('user')
    ->select('user_id', 'status', DB::raw('COUNT(*) as count'))
    ->groupBy('user_id', 'status')
    ->get();
```

### Agrupar por Fecha

```php
// Query Builder - agrupar por mes
$postsByMonth = DB::table('posts')
    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
    ->groupBy('month')
    ->orderBy('month')
    ->get();

// Eloquent - agrupar por mes
$postsByMonth = Post::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
    ->groupBy('month')
    ->orderBy('month')
    ->get();
```

## HAVING Clause

### Filtrar Agregados

```php
// Query Builder - filtrar por agregado
$popularPosts = DB::table('posts')
    ->select('user_id', DB::raw('SUM(views_count) as total_views'))
    ->groupBy('user_id')
    ->having('total_views', '>', 1000)
    ->get();

// Eloquent - filtrar por agregado
$popularPosts = Post::select('user_id', DB::raw('SUM(views_count) as total_views'))
    ->groupBy('user_id')
    ->having('total_views', '>', 1000)
    ->get();
```

### Múltiples Condiciones HAVING

```php
// Query Builder - múltiples condiciones HAVING
$activeUsers = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.id', 'users.name', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id', 'users.name')
    ->having('post_count', '>', 5)
    ->having('post_count', '<', 50)
    ->get();

// Eloquent - múltiples condiciones HAVING
$activeUsers = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.id', 'users.name', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id', 'users.name')
    ->having('post_count', '>', 5)
    ->having('post_count', '<', 50)
    ->get();
```

## Agregados con Relaciones

### Contar Relaciones

```php
// Eloquent - contar relaciones
$users = User::withCount('posts')->get();

foreach ($users as $user) {
    echo "Usuario: {$user->name}, Posts: {$user->posts_count}\n";
}

// Eloquent - contar múltiples relaciones
$users = User::withCount(['posts', 'comments'])->get();

foreach ($users as $user) {
    echo "Usuario: {$user->name}, Posts: {$user->posts_count}, Comentarios: {$user->comments_count}\n";
}
```

### Contar Relaciones con Condiciones

```php
// Eloquent - contar relaciones con condiciones
$users = User::withCount([
    'posts',
    'posts as published_posts_count' => function ($query) {
        $query->where('status', 'published');
    }
])->get();

foreach ($users as $user) {
    echo "Usuario: {$user->name}, Total Posts: {$user->posts_count}, Publicados: {$user->published_posts_count}\n";
}
```

### Agregados en Relaciones

```php
// Eloquent - agregados en relaciones
$users = User::with(['posts' => function ($query) {
    $query->select('user_id', DB::raw('SUM(views_count) as total_views'))
          ->groupBy('user_id');
}])->get();

// O usar withSum, withAvg, etc.
$users = User::withSum('posts', 'views_count')->get();
$users = User::withAvg('posts', 'views_count')->get();
$users = User::withMax('posts', 'views_count')->get();
$users = User::withMin('posts', 'views_count')->get();
```

## Ejemplos Prácticos

### Dashboard de Estadísticas

```php
public function getDashboardStats()
{
    // Estadísticas generales
    $generalStats = DB::table('users')
        ->selectRaw('
            COUNT(*) as total_users,
            COUNT(CASE WHEN status = "active" THEN 1 END) as active_users,
            COUNT(CASE WHEN created_at >= ? THEN 1 END) as new_users
        ', [now()->subDays(30)])
        ->first();

    // Estadísticas de posts
    $postStats = DB::table('posts')
        ->selectRaw('
            COUNT(*) as total_posts,
            COUNT(CASE WHEN status = "published" THEN 1 END) as published_posts,
            SUM(views_count) as total_views,
            AVG(views_count) as average_views
        ')
        ->first();

    // Estadísticas de comentarios
    $commentStats = DB::table('comments')
        ->selectRaw('
            COUNT(*) as total_comments,
            AVG(rating) as average_rating,
            COUNT(CASE WHEN rating >= 4 THEN 1 END) as positive_comments
        ')
        ->first();

    return [
        'users' => $generalStats,
        'posts' => $postStats,
        'comments' => $commentStats
    ];
}
```

### Reporte de Actividad por Usuario

```php
public function getUserActivityReport()
{
    return DB::table('users')
        ->leftJoin('posts', 'users.id', '=', 'posts.user_id')
        ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
        ->selectRaw('
            users.name,
            users.email,
            COUNT(DISTINCT posts.id) as posts_count,
            COUNT(DISTINCT comments.id) as comments_count,
            SUM(posts.views_count) as total_views,
            AVG(comments.rating) as average_rating
        ')
        ->groupBy('users.id', 'users.name', 'users.email')
        ->having('posts_count', '>', 0)
        ->orderBy('posts_count', 'desc')
        ->get();
}
```

### Análisis de Tendencias

```php
public function getTrendAnalysis()
{
    // Posts por mes
    $postsByMonth = Post::selectRaw('
        DATE_FORMAT(created_at, "%Y-%m") as month,
        COUNT(*) as posts_count,
        SUM(views_count) as total_views,
        AVG(views_count) as average_views
    ')
    ->groupBy('month')
    ->orderBy('month')
    ->get();

    // Comentarios por mes
    $commentsByMonth = Comment::selectRaw('
        DATE_FORMAT(created_at, "%Y-%m") as month,
        COUNT(*) as comments_count,
        AVG(rating) as average_rating
    ')
    ->groupBy('month')
    ->orderBy('month')
    ->get();

    return [
        'posts_trend' => $postsByMonth,
        'comments_trend' => $commentsByMonth
    ];
}
```

### Top Performers

```php
public function getTopPerformers()
{
    // Top usuarios por posts
    $topPosters = User::withCount('posts')
        ->having('posts_count', '>', 0)
        ->orderBy('posts_count', 'desc')
        ->limit(10)
        ->get();

    // Top posts por vistas
    $topPosts = Post::select('title', 'views_count', 'user_id')
        ->with('user:id,name')
        ->where('status', 'published')
        ->orderBy('views_count', 'desc')
        ->limit(10)
        ->get();

    // Top comentarios por rating
    $topComments = Comment::select('content', 'rating', 'user_id', 'post_id')
        ->with(['user:id,name', 'post:id,title'])
        ->where('rating', '>=', 4)
        ->orderBy('rating', 'desc')
        ->limit(10)
        ->get();

    return [
        'top_posters' => $topPosters,
        'top_posts' => $topPosts,
        'top_comments' => $topComments
    ];
}
```

## Optimización de Agregados

### Índices para Agregados

```sql
-- Índices recomendados para agregados
CREATE INDEX idx_posts_status_views ON posts(status, views_count);
CREATE INDEX idx_comments_rating ON comments(rating);
CREATE INDEX idx_users_status_created ON users(status, created_at);
```

### Agregados con Subconsultas

```php
// Query Builder - subconsulta en agregado
$usersWithPostCount = DB::table('users')
    ->select('users.*', DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count'))
    ->get();

// Eloquent - subconsulta en agregado
$usersWithPostCount = User::select('*', DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count'))
    ->get();
```

### Agregados con Expresiones Condicionales

```php
// Query Builder - expresiones condicionales
$userStats = DB::table('users')
    ->selectRaw('
        COUNT(*) as total_users,
        SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as inactive_users,
        SUM(CASE WHEN status = "banned" THEN 1 ELSE 0 END) as banned_users
    ')
    ->first();

// Eloquent - expresiones condicionales
$userStats = User::selectRaw('
    COUNT(*) as total_users,
    SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as inactive_users,
    SUM(CASE WHEN status = "banned" THEN 1 ELSE 0 END) as banned_users
')->first();
```

## Mejores Prácticas

### 1. Usar Índices Apropiados

```php
// ✅ Asegurar que las columnas usadas en agregados tengan índices
// status, views_count, rating, created_at, etc.
```

### 2. Limitar Resultados de Agregados

```php
// ✅ Usar LIMIT para agregados grandes
$topUsers = User::withCount('posts')
    ->orderBy('posts_count', 'desc')
    ->limit(100)
    ->get();
```

### 3. Usar Agregados en Lugar de Contar en PHP

```php
// ❌ Ineficiente
$users = User::all();
$activeCount = $users->filter(function($user) {
    return $user->status === 'active';
})->count();

// ✅ Eficiente
$activeCount = User::where('status', 'active')->count();
```

### 4. Optimizar Agregados con Relaciones

```php
// ✅ Usar withCount en lugar de cargar relaciones
$users = User::withCount('posts')->get();

// ❌ Evitar cargar toda la relación solo para contar
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->count(); // N+1 problem
}
```

### 5. Usar Agregados para Validaciones

```php
// ✅ Validar con agregados
$userPostCount = User::where('id', $userId)->withCount('posts')->first();

if ($userPostCount->posts_count > 10) {
    throw new Exception('Usuario ha excedido el límite de posts');
}
```

## Debugging de Agregados

### Ver SQL Generado

```php
// Ver SQL de agregados
$query = User::withCount('posts');
dd($query->toSql()); // Muestra SQL sin ejecutar

$result = $query->get();
dd($result); // Muestra resultados
```

### Logging de Agregados

```php
// Habilitar logging para debug
DB::enableQueryLog();

$stats = User::withCount('posts')->get();

dd(DB::getQueryLog()); // Muestra todas las consultas ejecutadas
```

---

**Próximo**: [Cláusula de Selección](./05-clausula-seleccion.md)
