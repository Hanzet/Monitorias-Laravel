# 7. Cláusula Inner Join

## Introducción

Los INNER JOINs permiten combinar registros de dos o más tablas basándose en una condición de relación. Laravel proporciona métodos intuitivos para trabajar con joins.

## Join Básico

### join() - Join Simple

```php
// Query Builder - join básico
$posts = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name')
    ->get();

// Eloquent - join básico
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name')
    ->get();
```

### join() con Condiciones Adicionales

```php
// Query Builder - join con condiciones adicionales
$posts = DB::table('posts')
    ->join('users', function($join) {
        $join->on('posts.user_id', '=', 'users.id')
             ->where('users.status', '=', 'active');
    })
    ->select('posts.*', 'users.name as author_name')
    ->get();

// Eloquent - join con condiciones adicionales
$posts = Post::join('users', function($join) {
    $join->on('posts.user_id', '=', 'users.id')
         ->where('users.status', '=', 'active');
})
->select('posts.*', 'users.name as author_name')
->get();
```

## Múltiples Joins

### join() Múltiple

```php
// Query Builder - múltiples joins
$comments = DB::table('comments')
    ->join('users', 'comments.user_id', '=', 'users.id')
    ->join('posts', 'comments.post_id', '=', 'posts.id')
    ->select(
        'comments.*',
        'users.name as commenter_name',
        'posts.title as post_title'
    )
    ->get();

// Eloquent - múltiples joins
$comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
    ->join('posts', 'comments.post_id', '=', 'posts.id')
    ->select(
        'comments.*',
        'users.name as commenter_name',
        'posts.title as post_title'
    )
    ->get();
```

### join() con Condiciones Complejas

```php
// Query Builder - joins con condiciones complejas
$posts = DB::table('posts')
    ->join('users', function($join) {
        $join->on('posts.user_id', '=', 'users.id')
             ->where('users.status', '=', 'active')
             ->where('users.created_at', '>=', now()->subYear());
    })
    ->join('comments', function($join) {
        $join->on('posts.id', '=', 'comments.post_id')
             ->where('comments.rating', '>=', 4);
    })
    ->select('posts.*', 'users.name as author_name', DB::raw('COUNT(comments.id) as comment_count'))
    ->groupBy('posts.id', 'users.name')
    ->get();

// Eloquent - joins con condiciones complejas
$posts = Post::join('users', function($join) {
    $join->on('posts.user_id', '=', 'users.id')
         ->where('users.status', '=', 'active')
         ->where('users.created_at', '>=', now()->subYear());
})
->join('comments', function($join) {
    $join->on('posts.id', '=', 'comments.post_id')
         ->where('comments.rating', '>=', 4);
})
->select('posts.*', 'users.name as author_name', DB::raw('COUNT(comments.id) as comment_count'))
->groupBy('posts.id', 'users.name')
->get();
```

## Joins con Agregados

### join() con COUNT

```php
// Query Builder - join con count
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->get();

// Eloquent - join con count
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->get();
```

### join() con SUM

```php
// Query Builder - join con sum
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('SUM(posts.views_count) as total_views'))
    ->groupBy('users.id')
    ->get();

// Eloquent - join con sum
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('SUM(posts.views_count) as total_views'))
    ->groupBy('users.id')
    ->get();
```

### join() con Múltiples Agregados

```php
// Query Builder - múltiples agregados
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->join('comments', 'users.id', '=', 'comments.user_id')
    ->select(
        'users.*',
        DB::raw('COUNT(DISTINCT posts.id) as post_count'),
        DB::raw('COUNT(DISTINCT comments.id) as comment_count'),
        DB::raw('SUM(posts.views_count) as total_views'),
        DB::raw('AVG(comments.rating) as avg_rating')
    )
    ->groupBy('users.id')
    ->get();

// Eloquent - múltiples agregados
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->join('comments', 'users.id', '=', 'comments.user_id')
    ->select(
        'users.*',
        DB::raw('COUNT(DISTINCT posts.id) as post_count'),
        DB::raw('COUNT(DISTINCT comments.id) as comment_count'),
        DB::raw('SUM(posts.views_count) as total_views'),
        DB::raw('AVG(comments.rating) as avg_rating')
    )
    ->groupBy('users.id')
    ->get();
```

## Joins con Filtros

### join() con WHERE

```php
// Query Builder - join con where
$posts = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->where('posts.status', 'published')
    ->where('users.status', 'active')
    ->select('posts.*', 'users.name as author_name')
    ->get();

// Eloquent - join con where
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->where('posts.status', 'published')
    ->where('users.status', 'active')
    ->select('posts.*', 'users.name as author_name')
    ->get();
```

### join() con HAVING

```php
// Query Builder - join con having
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->having('post_count', '>', 5)
    ->get();

// Eloquent - join con having
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->having('post_count', '>', 5)
    ->get();
```

## Joins con Ordenamiento

### join() con ORDER BY

```php
// Query Builder - join con order by
$users = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->orderBy('post_count', 'desc')
    ->get();

// Eloquent - join con order by
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->orderBy('post_count', 'desc')
    ->get();
```

### join() con Múltiples Ordenamientos

```php
// Query Builder - múltiples ordenamientos
$posts = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->join('comments', 'posts.id', '=', 'comments.post_id')
    ->select(
        'posts.*',
        'users.name as author_name',
        DB::raw('COUNT(comments.id) as comment_count'),
        DB::raw('AVG(comments.rating) as avg_rating')
    )
    ->groupBy('posts.id', 'users.name')
    ->orderBy('comment_count', 'desc')
    ->orderBy('avg_rating', 'desc')
    ->get();

// Eloquent - múltiples ordenamientos
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->join('comments', 'posts.id', '=', 'comments.post_id')
    ->select(
        'posts.*',
        'users.name as author_name',
        DB::raw('COUNT(comments.id) as comment_count'),
        DB::raw('AVG(comments.rating) as avg_rating')
    )
    ->groupBy('posts.id', 'users.name')
    ->orderBy('comment_count', 'desc')
    ->orderBy('avg_rating', 'desc')
    ->get();
```

## Ejemplos Prácticos

### Dashboard de Usuario

```php
public function getUserDashboard($userId)
{
    return User::join('posts', 'users.id', '=', 'posts.user_id')
        ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
        ->select(
            'users.*',
            DB::raw('COUNT(DISTINCT posts.id) as post_count'),
            DB::raw('COUNT(DISTINCT comments.id) as comment_count'),
            DB::raw('SUM(posts.views_count) as total_views'),
            DB::raw('AVG(comments.rating) as avg_rating')
        )
        ->where('users.id', $userId)
        ->groupBy('users.id')
        ->first();
}
```

### Reporte de Posts con Autores

```php
public function getPostsWithAuthors()
{
    return Post::join('users', 'posts.user_id', '=', 'users.id')
        ->leftJoin('comments', 'posts.id', '=', 'comments.post_id')
        ->select(
            'posts.*',
            'users.name as author_name',
            'users.email as author_email',
            DB::raw('COUNT(comments.id) as comment_count'),
            DB::raw('AVG(comments.rating) as avg_rating')
        )
        ->where('posts.status', 'published')
        ->groupBy('posts.id', 'users.name', 'users.email')
        ->orderBy('posts.views_count', 'desc')
        ->get();
}
```

### Top Usuarios por Actividad

```php
public function getTopActiveUsers()
{
    return User::join('posts', 'users.id', '=', 'posts.user_id')
        ->leftJoin('comments', 'users.id', '=', 'comments.user_id')
        ->select(
            'users.*',
            DB::raw('COUNT(DISTINCT posts.id) as post_count'),
            DB::raw('COUNT(DISTINCT comments.id) as comment_count'),
            DB::raw('SUM(posts.views_count) as total_views'),
            DB::raw('AVG(comments.rating) as avg_rating')
        )
        ->where('users.status', 'active')
        ->groupBy('users.id')
        ->having('post_count', '>', 0)
        ->orderBy('total_views', 'desc')
        ->limit(10)
        ->get();
}
```

### Análisis de Comentarios

```php
public function getCommentAnalysis()
{
    return Comment::join('users', 'comments.user_id', '=', 'users.id')
        ->join('posts', 'comments.post_id', '=', 'posts.id')
        ->select(
            'comments.*',
            'users.name as commenter_name',
            'posts.title as post_title',
            'posts.views_count as post_views'
        )
        ->where('comments.rating', '>=', 4)
        ->orderBy('comments.created_at', 'desc')
        ->get();
}
```

### Estadísticas por Mes

```php
public function getMonthlyStats()
{
    return Post::join('users', 'posts.user_id', '=', 'users.id')
        ->leftJoin('comments', 'posts.id', '=', 'comments.post_id')
        ->select(
            DB::raw('DATE_FORMAT(posts.created_at, "%Y-%m") as month'),
            DB::raw('COUNT(DISTINCT posts.id) as post_count'),
            DB::raw('COUNT(DISTINCT users.id) as active_authors'),
            DB::raw('COUNT(comments.id) as comment_count'),
            DB::raw('SUM(posts.views_count) as total_views'),
            DB::raw('AVG(comments.rating) as avg_rating')
        )
        ->groupBy(DB::raw('DATE_FORMAT(posts.created_at, "%Y-%m")'))
        ->orderBy('month')
        ->get();
}
```

## Optimización de Joins

### Índices para Joins

```sql
-- Índices recomendados para joins
CREATE INDEX idx_posts_user_id ON posts(user_id);
CREATE INDEX idx_comments_user_id ON comments(user_id);
CREATE INDEX idx_comments_post_id ON comments(post_id);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_posts_status ON posts(status);
```

### Evitar Joins Innecesarios

```php
// ❌ Join innecesario
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*')
    ->get();

// ✅ Usar relaciones de Eloquent
$users = User::with('posts')->get();
```

### Usar LEFT JOIN cuando sea Apropiado

```php
// ✅ LEFT JOIN para incluir usuarios sin posts
$users = User::leftJoin('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->get();

// ❌ INNER JOIN excluye usuarios sin posts
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->get();
```

## Mejores Prácticas

### 1. Usar Aliases para Claridad

```php
// ✅ Usar aliases descriptivos
$posts = Post::join('users as authors', 'posts.user_id', '=', 'authors.id')
    ->select('posts.*', 'authors.name as author_name')
    ->get();
```

### 2. Especificar Columnas en SELECT

```php
// ✅ Especificar columnas necesarias
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.id', 'posts.title', 'posts.views_count', 'users.name as author_name')
    ->get();

// ❌ Evitar SELECT *
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.*')
    ->get();
```

### 3. Usar Condiciones en JOIN

```php
// ✅ Usar condiciones en JOIN para mejor rendimiento
$posts = Post::join('users', function($join) {
    $join->on('posts.user_id', '=', 'users.id')
         ->where('users.status', '=', 'active');
})->get();

// ❌ Evitar condiciones en WHERE después del JOIN
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->where('users.status', '=', 'active')
    ->get();
```

### 4. Optimizar Agregados

```php
// ✅ Usar DISTINCT cuando sea necesario
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->join('comments', 'users.id', '=', 'comments.user_id')
    ->select('users.*',
        DB::raw('COUNT(DISTINCT posts.id) as post_count'),
        DB::raw('COUNT(DISTINCT comments.id) as comment_count')
    )
    ->groupBy('users.id')
    ->get();
```

## Debugging de Joins

### Ver SQL Generado

```php
// Ver SQL de join
$query = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name');
dd($query->toSql()); // Muestra SQL sin ejecutar

$result = $query->get();
dd($result); // Muestra resultados
```

### Verificar Relaciones

```php
// Verificar que los joins funcionan correctamente
$post = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name')
    ->first();

dd($post); // Verificar que los datos están correctos
```

---

**Próximo**: [Cláusula Where](./08-clausula-where.md)
