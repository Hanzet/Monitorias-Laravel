# Cláusulas Condicionales en Laravel

Las cláusulas condicionales permiten construir consultas dinámicas en Laravel, agregando condiciones solo si ciertos parámetros o variables están presentes. Los métodos más usados son `when()` y `unless()`.

## Conceptos Básicos

### 1. When

```php
// Agregar condición solo si $status existe
$status = $request->status;
$users = User::when($status, function($query, $status) {
    return $query->where('status', $status);
})->get();

// Agregar múltiples condiciones dinámicamente
$category = $request->category;
$author = $request->author;
$posts = Post::query()
    ->when($category, function($query, $category) {
        return $query->where('category', $category);
    })
    ->when($author, function($query, $author) {
        return $query->whereHas('user', function($q) use ($author) {
            $q->where('name', 'like', "%{$author}%");
        });
    })
    ->get();
```

### 2. Unless

```php
// Agregar condición solo si $excludeDrafts NO existe
$excludeDrafts = $request->exclude_drafts;
$posts = Post::query()
    ->unless($excludeDrafts, function($query) {
        return $query->where('status', 'draft');
    })
    ->get();
```

## Uso con Arrays y Filtros Dinámicos

```php
// Filtros dinámicos desde un array de parámetros
$filters = $request->only(['status', 'role', 'category']);
$query = User::query();
foreach ($filters as $field => $value) {
    if ($value) {
        $query->where($field, $value);
    }
}
$users = $query->get();
```

## Uso con Relaciones

```php
// Agregar condición sobre relación solo si $hasPosts existe
$hasPosts = $request->has_posts;
$users = User::when($hasPosts, function($query) {
    return $query->whereHas('posts');
})->get();
```

## Uso con Subconsultas y Expresiones

```php
// Condición dinámica con subconsulta
$minComments = $request->min_comments;
$posts = Post::when($minComments, function($query, $minComments) {
    return $query->whereHas('comments', function($q) use ($minComments) {
        $q->havingRaw('COUNT(*) >= ?', [$minComments]);
    });
})->get();
```

## Casos de Uso Comunes

### 1. Búsquedas Avanzadas

```php
// Búsqueda avanzada con múltiples parámetros opcionales
$search = $request->search;
$status = $request->status;
$category = $request->category;
$posts = Post::query()
    ->when($search, function($query, $search) {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
        });
    })
    ->when($status, function($query, $status) {
        return $query->where('status', $status);
    })
    ->when($category, function($query, $category) {
        return $query->where('category', $category);
    })
    ->get();
```

### 2. Filtros de Panel de Administración

```php
// Filtros condicionales en panel de administración
$filters = $request->only(['role', 'email_verified', 'active']);
$query = User::query();
if ($filters['role']) {
    $query->where('role', $filters['role']);
}
if ($filters['email_verified']) {
    $query->whereNotNull('email_verified_at');
}
if ($filters['active']) {
    $query->where('status', 'active');
}
$users = $query->get();
```

### 3. Reportes Dinámicos

```php
// Reporte de posts con filtros opcionales
$from = $request->from;
$to = $request->to;
$posts = Post::query()
    ->when($from, function($query, $from) {
        return $query->where('created_at', '>=', $from);
    })
    ->when($to, function($query, $to) {
        return $query->where('created_at', '<=', $to);
    })
    ->get();
```

## Mejores Prácticas

-   Usar `when()` y `unless()` para evitar ifs anidados y mantener el código limpio.
-   Validar los parámetros antes de usarlos en las consultas.
-   Encadenar múltiples condiciones para consultas flexibles.
-   Usar funciones anónimas para lógica compleja.

## Ejercicios Prácticos

### Ejercicio 1: Filtros Dinámicos

```php
// Crear un filtro que permita buscar usuarios por:
// - Rol (opcional)
// - Estado (opcional)
// - Email verificado (opcional)

$role = $request->role;
$status = $request->status;
$emailVerified = $request->email_verified;
$users = User::query()
    ->when($role, function($query, $role) {
        return $query->where('role', $role);
    })
    ->when($status, function($query, $status) {
        return $query->where('status', $status);
    })
    ->when($emailVerified, function($query) {
        return $query->whereNotNull('email_verified_at');
    })
    ->get();
```

### Ejercicio 2: Reporte Condicional

```php
// Crear un reporte de posts filtrando por fecha y categoría si existen
$from = $request->from;
$to = $request->to;
$category = $request->category;
$posts = Post::query()
    ->when($from, function($query, $from) {
        return $query->where('created_at', '>=', $from);
    })
    ->when($to, function($query, $to) {
        return $query->where('created_at', '<=', $to);
    })
    ->when($category, function($query, $category) {
        return $query->where('category', $category);
    })
    ->get();
```

### Ejercicio 3: Búsqueda Avanzada

```php
// Crear una búsqueda avanzada de usuarios
$search = $request->search;
$users = User::when($search, function($query, $search) {
    return $query->where(function($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
    });
})->get();
```

## Resumen

Las cláusulas condicionales en Laravel permiten construir consultas flexibles y limpias, adaptándose a los parámetros disponibles y evitando código repetitivo o anidado innecesario.
