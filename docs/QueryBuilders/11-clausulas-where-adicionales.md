# Cláusulas Where Adicionales en Laravel

Laravel proporciona múltiples variaciones de la cláusula `where` para manejar diferentes tipos de condiciones y casos de uso específicos.

## WhereNull y WhereNotNull

### 1. WhereNull

```php
// Buscar usuarios sin email verificado
$users = User::whereNull('email_verified_at')->get();

// Buscar posts sin fecha de publicación
$posts = Post::whereNull('published_at')->get();

// Buscar comentarios sin fecha de eliminación
$comments = Comment::whereNull('deleted_at')->get();
```

### 2. WhereNotNull

```php
// Buscar usuarios con email verificado
$users = User::whereNotNull('email_verified_at')->get();

// Buscar posts publicados
$posts = Post::whereNotNull('published_at')->get();

// Buscar comentarios activos
$comments = Comment::whereNotNull('created_at')->get();
```

## WhereDate, WhereTime, WhereDay, WhereMonth, WhereYear

### 1. WhereDate

```php
// Buscar posts publicados en una fecha específica
$posts = Post::whereDate('created_at', '2024-01-15')->get();

// Buscar usuarios registrados hoy
$users = User::whereDate('created_at', today())->get();

// Buscar comentarios de ayer
$comments = Comment::whereDate('created_at', yesterday())->get();
```

### 2. WhereTime

```php
// Buscar posts creados a una hora específica
$posts = Post::whereTime('created_at', '14:30:00')->get();

// Buscar usuarios registrados en la mañana
$users = User::whereTime('created_at', '>=', '06:00:00')
    ->whereTime('created_at', '<=', '12:00:00')
    ->get();
```

### 3. WhereDay

```php
// Buscar posts creados el día 15 de cualquier mes
$posts = Post::whereDay('created_at', 15)->get();

// Buscar usuarios registrados en días específicos
$users = User::whereIn(DB::raw('DAY(created_at)'), [1, 15, 30])->get();
```

### 4. WhereMonth

```php
// Buscar posts creados en enero
$posts = Post::whereMonth('created_at', 1)->get();

// Buscar usuarios registrados en meses específicos
$users = User::whereIn(DB::raw('MONTH(created_at)'), [1, 6, 12])->get();
```

### 5. WhereYear

```php
// Buscar posts creados en 2024
$posts = Post::whereYear('created_at', 2024)->get();

// Buscar usuarios registrados en años específicos
$users = User::whereIn(DB::raw('YEAR(created_at)'), [2022, 2023, 2024])->get();
```

## WhereBetween y WhereNotBetween

### 1. WhereBetween

```php
// Buscar usuarios con edad entre 18 y 65
$users = User::whereBetween('age', [18, 65])->get();

// Buscar posts con vistas entre 100 y 1000
$posts = Post::whereBetween('views_count', [100, 1000])->get();

// Buscar comentarios creados en un rango de fechas
$comments = Comment::whereBetween('created_at', [
    now()->subDays(7),
    now()
])->get();
```

### 2. WhereNotBetween

```php
// Buscar usuarios con edad fuera del rango 18-65
$users = User::whereNotBetween('age', [18, 65])->get();

// Buscar posts con vistas fuera del rango 100-1000
$posts = Post::whereNotBetween('views_count', [100, 1000])->get();
```

## WhereIn y WhereNotIn

### 1. WhereIn

```php
// Buscar usuarios con roles específicos
$users = User::whereIn('role', ['admin', 'moderator', 'editor'])->get();

// Buscar posts de categorías específicas
$posts = Post::whereIn('category', ['tech', 'science', 'health'])->get();

// Buscar comentarios de usuarios específicos
$comments = Comment::whereIn('user_id', [1, 5, 10, 15])->get();
```

### 2. WhereNotIn

```php
// Excluir usuarios con roles específicos
$users = User::whereNotIn('role', ['guest', 'banned'])->get();

// Excluir posts de categorías específicas
$posts = Post::whereNotIn('category', ['private', 'archived'])->get();
```

## WhereExists y WhereNotExists

### 1. WhereExists

```php
// Buscar usuarios que tienen posts
$users = User::whereExists(function($query) {
    $query->select(DB::raw(1))
        ->from('posts')
        ->whereRaw('posts.user_id = users.id');
})->get();

// Buscar posts que tienen comentarios
$posts = Post::whereExists(function($query) {
    $query->select(DB::raw(1))
        ->from('comments')
        ->whereRaw('comments.post_id = posts.id');
})->get();
```

### 2. WhereNotExists

```php
// Buscar usuarios que no tienen posts
$users = User::whereNotExists(function($query) {
    $query->select(DB::raw(1))
        ->from('posts')
        ->whereRaw('posts.user_id = users.id');
})->get();
```

## WhereRaw y OrWhereRaw

### 1. WhereRaw

```php
// Buscar usuarios con nombre que contenga 'admin' (case insensitive)
$users = User::whereRaw('LOWER(name) LIKE ?', ['%admin%'])->get();

// Buscar posts con contenido que contenga palabras específicas
$posts = Post::whereRaw('LOWER(content) LIKE ? OR LOWER(title) LIKE ?', [
    '%laravel%',
    '%php%'
])->get();

// Buscar comentarios con longitud específica
$comments = Comment::whereRaw('LENGTH(content) > ?', [100])->get();
```

### 2. OrWhereRaw

```php
// Buscar usuarios con email o nombre que contenga 'admin'
$users = User::where('email', 'like', '%admin%')
    ->orWhereRaw('LOWER(name) LIKE ?', ['%admin%'])
    ->get();
```

## WhereColumn

### 1. Comparación entre Columnas

```php
// Buscar posts donde updated_at es mayor que created_at
$posts = Post::whereColumn('updated_at', '>', 'created_at')->get();

// Buscar usuarios donde last_login_at es igual a created_at
$users = User::whereColumn('last_login_at', '=', 'created_at')->get();

// Buscar comentarios donde updated_at es diferente a created_at
$comments = Comment::whereColumn('updated_at', '!=', 'created_at')->get();
```

### 2. Múltiples Comparaciones de Columnas

```php
// Buscar posts con múltiples condiciones de columnas
$posts = Post::whereColumn([
    ['updated_at', '>', 'created_at'],
    ['published_at', '!=', null],
    ['views_count', '>', 'comments_count']
])->get();
```

## WhereJsonContains y WhereJsonLength

### 1. WhereJsonContains

```php
// Buscar usuarios con tags específicos (columna JSON)
$users = User::whereJsonContains('tags', 'developer')->get();

// Buscar posts con categorías específicas (columna JSON)
$posts = Post::whereJsonContains('categories', ['tech', 'laravel'])->get();
```

### 2. WhereJsonLength

```php
// Buscar usuarios con más de 3 tags
$users = User::whereJsonLength('tags', '>', 3)->get();

// Buscar posts con exactamente 2 categorías
$posts = Post::whereJsonLength('categories', '=', 2)->get();
```

## When y Unless

### 1. When

```php
// Aplicar condición solo si el parámetro existe
$status = $request->status;
$category = $request->category;

$posts = Post::query()
    ->when($status, function($query, $status) {
        return $query->where('status', $status);
    })
    ->when($category, function($query, $category) {
        return $query->where('category', $category);
    })
    ->get();
```

### 2. Unless

```php
// Aplicar condición solo si el parámetro NO existe
$excludeDrafts = $request->exclude_drafts;

$posts = Post::query()
    ->unless($excludeDrafts, function($query) {
        return $query->where('status', 'draft');
    })
    ->get();
```

## Casos de Uso Avanzados

### 1. Búsqueda Compleja

```php
// Búsqueda avanzada con múltiples condiciones
$search = $request->search;
$category = $request->category;
$dateFrom = $request->date_from;
$dateTo = $request->date_to;

$posts = Post::query()
    ->when($search, function($query, $search) {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhereRaw('LOWER(title) LIKE ?', ['%' . strtolower($search) . '%']);
        });
    })
    ->when($category, function($query, $category) {
        return $query->where('category', $category);
    })
    ->when($dateFrom && $dateTo, function($query) use ($dateFrom, $dateTo) {
        return $query->whereBetween('created_at', [$dateFrom, $dateTo]);
    })
    ->where('status', 'published')
    ->orderBy('created_at', 'desc')
    ->get();
```

### 2. Filtros Dinámicos

```php
// Sistema de filtros dinámico
public function filterUsers(Request $request)
{
    $query = User::query();

    // Filtros básicos
    $filters = $request->only(['status', 'role', 'email_verified']);

    foreach ($filters as $field => $value) {
        if ($value !== null && $value !== '') {
            if ($field === 'email_verified') {
                $query->when($value, function($q) {
                    return $q->whereNotNull('email_verified_at');
                }, function($q) {
                    return $q->whereNull('email_verified_at');
                });
            } else {
                $query->where($field, $value);
            }
        }
    }

    // Filtros de fecha
    if ($request->date_from) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->date_to) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    // Filtros de edad
    if ($request->age_min) {
        $query->where('age', '>=', $request->age_min);
    }

    if ($request->age_max) {
        $query->where('age', '<=', $request->age_max);
    }

    return $query->with(['posts', 'comments'])->get();
}
```

### 3. Reportes con Condiciones Complejas

```php
// Reporte de actividad de usuarios
public function getUserActivityReport()
{
    $now = now();

    return User::select('id', 'name', 'email', 'created_at')
        ->withCount(['posts', 'comments'])
        ->where('status', 'active')
        ->whereNotNull('email_verified_at')
        ->where(function($query) use ($now) {
            $query->where('last_login_at', '>=', $now->subDays(30))
                  ->orWhereHas('posts', function($q) use ($now) {
                      $q->where('created_at', '>=', $now->subDays(7));
                  })
                  ->orWhereHas('comments', function($q) use ($now) {
                      $q->where('created_at', '>=', $now->subDays(3));
                  });
        })
        ->whereNotIn('role', ['guest', 'banned'])
        ->orderBy('created_at', 'desc')
        ->get();
}
```

## Mejores Prácticas

### 1. Optimización de Consultas

```php
// Usar índices apropiados para las columnas más consultadas
// Evitar funciones en cláusulas WHERE cuando sea posible
// Usar select() para limitar columnas retornadas

$users = User::select('id', 'name', 'email', 'status')
    ->where('status', 'active')
    ->whereNotNull('email_verified_at')
    ->get();
```

### 2. Validación de Datos

```php
// Validar parámetros antes de usar en consultas
$search = $request->get('search', '');
$search = trim($search);

if (strlen($search) >= 3) {
    $users = User::where('name', 'like', "%{$search}%")
        ->orWhere('email', 'like', "%{$search}%")
        ->get();
} else {
    $users = collect(); // Retornar colección vacía si búsqueda muy corta
}
```

### 3. Seguridad

```php
// Usar parámetros en lugar de concatenación de strings
// Evitar inyección SQL

// ❌ Incorrecto
$users = User::whereRaw("name LIKE '%{$search}%'")->get();

// ✅ Correcto
$users = User::whereRaw('name LIKE ?', ["%{$search}%"])->get();
```

## Resumen

Las cláusulas where adicionales en Laravel proporcionan:

-   **Flexibilidad**: Múltiples formas de filtrar datos
-   **Precisión**: Condiciones específicas para diferentes tipos de datos
-   **Rendimiento**: Optimización de consultas con índices apropiados
-   **Seguridad**: Protección contra inyección SQL
-   **Mantenibilidad**: Código limpio y legible

Recuerda siempre usar las cláusulas apropiadas para cada caso de uso y optimizar las consultas para mejor rendimiento.
