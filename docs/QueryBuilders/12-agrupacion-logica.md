# Agrupación Lógica en Laravel

La agrupación lógica en Laravel permite organizar condiciones WHERE de manera estructurada usando paréntesis para controlar la precedencia de operadores lógicos.

## Conceptos Básicos

### 1. Agrupación Simple

```php
// Agrupar condiciones OR dentro de condiciones AND
$users = User::where('status', 'active')
    ->where(function($query) {
        $query->where('age', '>', 18)
            ->orWhere('is_admin', true);
    })
    ->get();

// Buscar usuarios activos Y (mayores de 18 O administradores)
```

### 2. Múltiples Grupos

```php
// Múltiples grupos de condiciones
$posts = Post::where('status', 'published')
    ->where(function($query) {
        $query->where('category', 'tech')
            ->orWhere('category', 'science');
    })
    ->where(function($query) {
        $query->where('views_count', '>', 100)
            ->orWhere('is_featured', true);
    })
    ->get();

// Posts publicados Y (tech O science) Y (más de 100 vistas O destacados)
```

## Agrupación con Relaciones

### 1. WhereHas con Agrupación

```php
// Usuarios con posts publicados O comentarios recientes
$users = User::where('status', 'active')
    ->where(function($query) {
        $query->whereHas('posts', function($q) {
            $q->where('status', 'published');
        })
        ->orWhereHas('comments', function($q) {
            $q->where('created_at', '>=', now()->subDays(7));
        });
    })
    ->get();
```

### 2. Relaciones Anidadas con Agrupación

```php
// Posts con comentarios de usuarios verificados O likes de usuarios activos
$posts = Post::where('status', 'published')
    ->where(function($query) {
        $query->whereHas('comments.user', function($q) {
            $q->where('email_verified_at', '!=', null);
        })
        ->orWhereHas('likes.user', function($q) {
            $q->where('status', 'active');
        });
    })
    ->get();
```

## Agrupación con Agregados

### 1. Agrupación con Count

```php
// Usuarios con más de 5 posts O más de 10 comentarios
$users = User::where('status', 'active')
    ->where(function($query) {
        $query->whereHas('posts', function($q) {
            $q->havingRaw('COUNT(*) > 5');
        })
        ->orWhereHas('comments', function($q) {
            $q->havingRaw('COUNT(*) > 10');
        });
    })
    ->get();
```

### 2. Agrupación con Sum

```php
// Posts con más de 1000 vistas totales O más de 50 comentarios
$posts = Post::where('status', 'published')
    ->where(function($query) {
        $query->whereHas('views', function($q) {
            $q->havingRaw('SUM(views_count) > 1000');
        })
        ->orWhere('comments_count', '>', 50);
    })
    ->get();
```

## Agrupación con Expresiones Raw

### 1. Agrupación con SQL Raw

```php
// Usuarios con edad mayor a 25 O registrados hace más de 1 año
$users = User::where('status', 'active')
    ->where(function($query) {
        $query->where('age', '>', 25)
            ->orWhere(DB::raw('DATEDIFF(NOW(), created_at)'), '>', 365);
    })
    ->get();
```

### 2. Agrupación con Subconsultas

```php
// Usuarios que han comentado O tienen posts con más de 100 vistas
$users = User::where('status', 'active')
    ->where(function($query) {
        $query->whereExists(function($q) {
            $q->select(DB::raw(1))
                ->from('comments')
                ->whereRaw('comments.user_id = users.id');
        })
        ->orWhereExists(function($q) {
            $q->select(DB::raw(1))
                ->from('posts')
                ->whereRaw('posts.user_id = users.id')
                ->where('views_count', '>', 100);
        });
    })
    ->get();
```

## Agrupación Compleja

### 1. Múltiples Niveles de Agrupación

```php
// Consulta compleja con múltiples grupos
$posts = Post::where('status', 'published')
    ->where(function($query) {
        $query->where('category', 'tech')
            ->orWhere('category', 'science')
            ->orWhere(function($q) {
                $q->where('category', 'health')
                    ->where('is_featured', true);
            });
    })
    ->where(function($query) {
        $query->where('views_count', '>', 100)
            ->orWhere('comments_count', '>', 10)
            ->orWhere(function($q) {
                $q->where('likes_count', '>', 50)
                    ->where('created_at', '>=', now()->subDays(30));
            });
    })
    ->get();
```

### 2. Agrupación con Joins

```php
// Posts con autor activo O con más de 50 comentarios
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->where(function($query) {
        $query->where('users.status', 'active')
            ->orWhere('posts.comments_count', '>', 50);
    })
    ->select('posts.*', 'users.name as author_name')
    ->get();
```

## Casos de Uso Avanzados

### 1. Búsqueda Avanzada

```php
// Sistema de búsqueda complejo
public function advancedSearch(Request $request)
{
    $search = $request->search;
    $category = $request->category;
    $dateFrom = $request->date_from;
    $dateTo = $request->date_to;

    return Post::where('status', 'published')
        ->when($search, function($query, $search) {
            return $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        })
        ->when($category, function($query, $category) {
            return $query->where(function($q) use ($category) {
                $q->where('category', $category)
                    ->orWhere('tags', 'like', "%{$category}%");
            });
        })
        ->when($dateFrom && $dateTo, function($query) use ($dateFrom, $dateTo) {
            return $query->where(function($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->orWhereBetween('updated_at', [$dateFrom, $dateTo]);
            });
        })
        ->get();
}
```

### 2. Filtros Dinámicos

```php
// Sistema de filtros con agrupación lógica
public function filterUsers(Request $request)
{
    $query = User::query();

    // Filtros básicos
    if ($request->status) {
        $query->where('status', $request->status);
    }

    // Filtros de actividad
    if ($request->has_activity) {
        $query->where(function($q) {
            $q->where('last_login_at', '>=', now()->subDays(30))
                ->orWhereHas('posts', function($postQuery) {
                    $postQuery->where('created_at', '>=', now()->subDays(7));
                })
                ->orWhereHas('comments', function($commentQuery) {
                    $commentQuery->where('created_at', '>=', now()->subDays(3));
                });
        });
    }

    // Filtros de roles
    if ($request->roles) {
        $query->where(function($q) use ($request) {
            foreach ($request->roles as $role) {
                $q->orWhere('role', $role);
            }
        });
    }

    return $query->get();
}
```

### 3. Reportes con Condiciones Complejas

```php
// Reporte de usuarios activos con actividad reciente
public function getActiveUsersReport()
{
    return User::where('status', 'active')
        ->whereNotNull('email_verified_at')
        ->where(function($query) {
            $query->where('last_login_at', '>=', now()->subDays(30))
                ->orWhere(function($q) {
                    $q->whereHas('posts', function($postQuery) {
                        $postQuery->where('created_at', '>=', now()->subDays(7));
                    })
                    ->orWhereHas('comments', function($commentQuery) {
                        $commentQuery->where('created_at', '>=', now()->subDays(3));
                    });
                });
        })
        ->whereNotIn('role', ['guest', 'banned'])
        ->withCount(['posts', 'comments'])
        ->get();
}
```

## Mejores Prácticas

### 1. Legibilidad del Código

```php
// Usar variables para mejorar legibilidad
$activeUsers = User::where('status', 'active')
    ->where(function($query) {
        $query->where('age', '>', 18)
            ->orWhere('is_admin', true);
    })
    ->get();

// O usar métodos separados
public function getActiveUsers()
{
    return User::where('status', 'active')
        ->where($this->getAgeOrAdminCondition())
        ->get();
}

private function getAgeOrAdminCondition()
{
    return function($query) {
        $query->where('age', '>', 18)
            ->orWhere('is_admin', true);
    };
}
```

### 2. Optimización de Consultas

```php
// Usar select() para limitar columnas
$users = User::select('id', 'name', 'email', 'status')
    ->where('status', 'active')
    ->where(function($query) {
        $query->where('age', '>', 18)
            ->orWhere('is_admin', true);
    })
    ->get();
```

### 3. Validación de Datos

```php
// Validar parámetros antes de usar en agrupaciones
public function searchPosts(Request $request)
{
    $search = trim($request->get('search', ''));

    $query = Post::where('status', 'published');

    if (strlen($search) >= 3) {
        $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
        });
    }

    return $query->get();
}
```

## Ejercicios Prácticos

### Ejercicio 1: Búsqueda de Usuarios

```php
// Crear una búsqueda que encuentre usuarios que:
// - Sean activos Y
// - (Tengan email verificado O sean administradores) Y
// - (Tengan más de 5 posts O más de 10 comentarios)

$users = User::where('status', 'active')
    ->where(function($query) {
        $query->whereNotNull('email_verified_at')
            ->orWhere('is_admin', true);
    })
    ->where(function($query) {
        $query->whereHas('posts', function($q) {
            $q->havingRaw('COUNT(*) > 5');
        })
        ->orWhereHas('comments', function($q) {
            $q->havingRaw('COUNT(*) > 10');
        });
    })
    ->get();
```

### Ejercicio 2: Filtros de Posts

```php
// Crear filtros para posts que:
// - Estén publicados Y
// - (Sean de categoría tech/science O tengan más de 100 vistas) Y
// - (Tengan comentarios recientes O sean destacados)

$posts = Post::where('status', 'published')
    ->where(function($query) {
        $query->whereIn('category', ['tech', 'science'])
            ->orWhere('views_count', '>', 100);
    })
    ->where(function($query) {
        $query->whereHas('comments', function($q) {
            $q->where('created_at', '>=', now()->subDays(7));
        })
        ->orWhere('is_featured', true);
    })
    ->get();
```

### Ejercicio 3: Reporte de Actividad

```php
// Crear un reporte de usuarios que:
// - Estén activos Y
// - (Se hayan registrado en los últimos 30 días O hayan hecho login recientemente) Y
// - (Tengan posts O comentarios en los últimos 7 días)

$activeUsers = User::where('status', 'active')
    ->where(function($query) {
        $query->where('created_at', '>=', now()->subDays(30))
            ->orWhere('last_login_at', '>=', now()->subDays(7));
    })
    ->where(function($query) {
        $query->whereHas('posts', function($q) {
            $q->where('created_at', '>=', now()->subDays(7));
        })
        ->orWhereHas('comments', function($q) {
            $q->where('created_at', '>=', now()->subDays(7));
        });
    })
    ->withCount(['posts', 'comments'])
    ->get();
```

## Resumen

La agrupación lógica en Laravel es fundamental para:

-   **Control de Precedencia**: Usar paréntesis para controlar el orden de evaluación
-   **Consultas Complejas**: Crear filtros avanzados con múltiples condiciones
-   **Legibilidad**: Organizar código de manera clara y estructurada
-   **Mantenibilidad**: Facilitar la modificación y extensión de consultas
-   **Rendimiento**: Optimizar consultas con agrupación apropiada

Recuerda siempre usar paréntesis para agrupar condiciones OR dentro de condiciones AND y mantener el código legible y mantenible.
