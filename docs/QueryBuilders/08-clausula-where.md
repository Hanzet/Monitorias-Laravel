# 8. Cláusula Where

## Introducción

La cláusula WHERE permite filtrar registros basándose en condiciones específicas. Laravel proporciona métodos flexibles y expresivos para construir condiciones de filtrado.

## Where Básico

### where() - Condición Simple

```php
// Query Builder - condición simple
$users = DB::table('users')
    ->where('status', 'active')
    ->get();

// Eloquent - condición simple
$users = User::where('status', 'active')->get();

// Query Builder - con operador explícito
$users = DB::table('users')
    ->where('age', '>', 18)
    ->get();

// Eloquent - con operador explícito
$users = User::where('age', '>', 18)->get();
```

### where() con Array

```php
// Query Builder - múltiples condiciones con array
$users = DB::table('users')
    ->where([
        ['status', '=', 'active'],
        ['age', '>', 18],
        ['email', 'like', '%@gmail.com']
    ])
    ->get();

// Eloquent - múltiples condiciones con array
$users = User::where([
    ['status', '=', 'active'],
    ['age', '>', 18],
    ['email', 'like', '%@gmail.com']
])->get();
```

## Operadores de Comparación

### Operadores Básicos

```php
// Igual (=)
$users = User::where('status', 'active')->get();

// Diferente (!=)
$users = User::where('status', '!=', 'banned')->get();

// Mayor que (>)
$users = User::where('age', '>', 18)->get();

// Mayor o igual (>=)
$users = User::where('age', '>=', 18)->get();

// Menor que (<)
$users = User::where('age', '<', 65)->get();

// Menor o igual (<=)
$users = User::where('age', '<=', 65)->get();

// LIKE
$users = User::where('name', 'like', '%John%')->get();

// NOT LIKE
$users = User::where('name', 'not like', '%Admin%')->get();
```

### Operadores de Patrón

```php
// Comienza con
$users = User::where('name', 'like', 'John%')->get();

// Termina con
$users = User::where('email', 'like', '%@gmail.com')->get();

// Contiene
$users = User::where('name', 'like', '%John%')->get();

// Patrón específico
$users = User::where('phone', 'like', '555-%')->get();
```

## Condiciones Múltiples

### where() Encadenado

```php
// Query Builder - múltiples where
$users = DB::table('users')
    ->where('status', 'active')
    ->where('age', '>', 18)
    ->where('email', 'like', '%@gmail.com')
    ->get();

// Eloquent - múltiples where
$users = User::where('status', 'active')
    ->where('age', '>', 18)
    ->where('email', 'like', '%@gmail.com')
    ->get();
```

### where() con Closure

```php
// Query Builder - con closure
$users = DB::table('users')
    ->where('status', 'active')
    ->where(function($query) {
        $query->where('age', '>', 18)
              ->orWhere('is_admin', true);
    })
    ->get();

// Eloquent - con closure
$users = User::where('status', 'active')
    ->where(function($query) {
        $query->where('age', '>', 18)
              ->orWhere('is_admin', true);
    })
    ->get();
```

## Condiciones con Arrays

### whereIn() - Valores en Array

```php
// Query Builder - whereIn
$users = DB::table('users')
    ->whereIn('status', ['active', 'pending'])
    ->get();

// Eloquent - whereIn
$users = User::whereIn('status', ['active', 'pending'])->get();

// Query Builder - whereIn con múltiples columnas
$users = DB::table('users')
    ->whereIn('id', [1, 2, 3, 4, 5])
    ->get();

// Eloquent - whereIn con múltiples columnas
$users = User::whereIn('id', [1, 2, 3, 4, 5])->get();
```

### whereNotIn() - Valores No en Array

```php
// Query Builder - whereNotIn
$users = DB::table('users')
    ->whereNotIn('status', ['banned', 'deleted'])
    ->get();

// Eloquent - whereNotIn
$users = User::whereNotIn('status', ['banned', 'deleted'])->get();
```

### whereBetween() - Rango de Valores

```php
// Query Builder - whereBetween
$users = DB::table('users')
    ->whereBetween('age', [18, 65])
    ->get();

// Eloquent - whereBetween
$users = User::whereBetween('age', [18, 65])->get();

// Query Builder - whereBetween con fechas
$users = DB::table('users')
    ->whereBetween('created_at', [now()->subDays(30), now()])
    ->get();

// Eloquent - whereBetween con fechas
$users = User::whereBetween('created_at', [now()->subDays(30), now()])->get();
```

### whereNotBetween() - Fuera de Rango

```php
// Query Builder - whereNotBetween
$users = DB::table('users')
    ->whereNotBetween('age', [18, 65])
    ->get();

// Eloquent - whereNotBetween
$users = User::whereNotBetween('age', [18, 65])->get();
```

## Condiciones de Nulos

### whereNull() - Valores Nulos

```php
// Query Builder - whereNull
$users = DB::table('users')
    ->whereNull('email_verified_at')
    ->get();

// Eloquent - whereNull
$users = User::whereNull('email_verified_at')->get();
```

### whereNotNull() - Valores No Nulos

```php
// Query Builder - whereNotNull
$users = DB::table('users')
    ->whereNotNull('email_verified_at')
    ->get();

// Eloquent - whereNotNull
$users = User::whereNotNull('email_verified_at')->get();
```

## Condiciones de Fecha

### whereDate() - Comparar Fechas

```php
// Query Builder - whereDate
$users = DB::table('users')
    ->whereDate('created_at', '2024-01-01')
    ->get();

// Eloquent - whereDate
$users = User::whereDate('created_at', '2024-01-01')->get();

// Query Builder - whereDate con operador
$users = DB::table('users')
    ->whereDate('created_at', '>=', '2024-01-01')
    ->get();

// Eloquent - whereDate con operador
$users = User::whereDate('created_at', '>=', '2024-01-01')->get();
```

### whereTime() - Comparar Tiempo

```php
// Query Builder - whereTime
$users = DB::table('users')
    ->whereTime('created_at', '09:00:00')
    ->get();

// Eloquent - whereTime
$users = User::whereTime('created_at', '09:00:00')->get();
```

### whereYear() - Comparar Año

```php
// Query Builder - whereYear
$users = DB::table('users')
    ->whereYear('created_at', 2024)
    ->get();

// Eloquent - whereYear
$users = User::whereYear('created_at', 2024)->get();
```

### whereMonth() - Comparar Mes

```php
// Query Builder - whereMonth
$users = DB::table('users')
    ->whereMonth('created_at', 1)
    ->get();

// Eloquent - whereMonth
$users = User::whereMonth('created_at', 1)->get();
```

### whereDay() - Comparar Día

```php
// Query Builder - whereDay
$users = DB::table('users')
    ->whereDay('created_at', 15)
    ->get();

// Eloquent - whereDay
$users = User::whereDay('created_at', 15)->get();
```

## Condiciones con Subconsultas

### whereExists() - Verificar Existencia

```php
// Query Builder - whereExists
$users = DB::table('users')
    ->whereExists(function($query) {
        $query->select(DB::raw(1))
              ->from('posts')
              ->whereRaw('posts.user_id = users.id')
              ->where('posts.status', 'published');
    })
    ->get();

// Eloquent - whereExists
$users = User::whereExists(function($query) {
    $query->select(DB::raw(1))
          ->from('posts')
          ->whereRaw('posts.user_id = users.id')
          ->where('posts.status', 'published');
})->get();
```

### whereNotExists() - Verificar No Existencia

```php
// Query Builder - whereNotExists
$users = DB::table('users')
    ->whereNotExists(function($query) {
        $query->select(DB::raw(1))
              ->from('posts')
              ->whereRaw('posts.user_id = users.id');
    })
    ->get();

// Eloquent - whereNotExists
$users = User::whereNotExists(function($query) {
    $query->select(DB::raw(1))
          ->from('posts')
          ->whereRaw('posts.user_id = users.id');
})->get();
```

## Condiciones Dinámicas

### when() - Condiciones Condicionales

```php
// Query Builder - when
$status = $request->get('status');
$users = DB::table('users')
    ->when($status, function($query, $status) {
        return $query->where('status', $status);
    })
    ->get();

// Eloquent - when
$status = $request->get('status');
$users = User::when($status, function($query, $status) {
    return $query->where('status', $status);
})->get();

// Query Builder - when con callback
$search = $request->get('search');
$users = DB::table('users')
    ->when($search, function($query, $search) {
        return $query->where('name', 'like', "%{$search}%");
    }, function($query) {
        return $query->where('status', 'active');
    })
    ->get();

// Eloquent - when con callback
$search = $request->get('search');
$users = User::when($search, function($query, $search) {
    return $query->where('name', 'like', "%{$search}%");
}, function($query) {
    return $query->where('status', 'active');
})->get();
```

### unless() - Condiciones Inversas

```php
// Query Builder - unless
$status = $request->get('status');
$users = DB::table('users')
    ->unless($status, function($query) {
        return $query->where('status', 'active');
    })
    ->get();

// Eloquent - unless
$status = $request->get('status');
$users = User::unless($status, function($query) {
    return $query->where('status', 'active');
})->get();
```

## Ejemplos Prácticos

### Búsqueda de Usuarios

```php
public function searchUsers(Request $request)
{
    $query = User::query();

    // Búsqueda por nombre o email
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Filtro por estado
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filtro por edad
    if ($request->filled('min_age')) {
        $query->where('age', '>=', $request->min_age);
    }

    if ($request->filled('max_age')) {
        $query->where('age', '<=', $request->max_age);
    }

    // Filtro por fecha de registro
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    // Filtro por usuarios con posts
    if ($request->boolean('has_posts')) {
        $query->whereExists(function($subQuery) {
            $subQuery->select(DB::raw(1))
                     ->from('posts')
                     ->whereRaw('posts.user_id = users.id');
        });
    }

    return $query->paginate(15);
}
```

### Filtros de Posts

```php
public function getFilteredPosts(Request $request)
{
    $query = Post::query();

    // Filtro por estado
    $query->when($request->status, function($q, $status) {
        return $q->where('status', $status);
    });

    // Filtro por autor
    $query->when($request->user_id, function($q, $userId) {
        return $q->where('user_id', $userId);
    });

    // Filtro por vistas mínimas
    $query->when($request->min_views, function($q, $minViews) {
        return $q->where('views_count', '>=', $minViews);
    });

    // Filtro por fecha de publicación
    $query->when($request->published_after, function($q, $date) {
        return $q->whereDate('published_at', '>=', $date);
    });

    // Filtro por posts con comentarios
    $query->when($request->has_comments, function($q) {
        return $q->whereExists(function($subQuery) {
            $subQuery->select(DB::raw(1))
                     ->from('comments')
                     ->whereRaw('comments.post_id = posts.id');
        });
    });

    return $query->with('user')->paginate(20);
}
```

### Análisis de Comentarios

```php
public function getCommentAnalysis(Request $request)
{
    $query = Comment::query();

    // Filtro por rating
    $query->when($request->min_rating, function($q, $rating) {
        return $q->where('rating', '>=', $rating);
    });

    // Filtro por usuario
    $query->when($request->user_id, function($q, $userId) {
        return $q->where('user_id', $userId);
    });

    // Filtro por post
    $query->when($request->post_id, function($q, $postId) {
        return $q->where('post_id', $postId);
    });

    // Filtro por fecha
    $query->when($request->date_from, function($q, $date) {
        return $q->whereDate('created_at', '>=', $date);
    });

    // Filtro por comentarios con contenido
    $query->when($request->has_content, function($q) {
        return $q->whereNotNull('content')
                 ->where('content', '!=', '');
    });

    return $query->with(['user', 'post'])->get();
}
```

## Optimización de Where

### Índices para Where

```sql
-- Índices recomendados para where
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_posts_status_user_id ON posts(status, user_id);
CREATE INDEX idx_comments_rating ON comments(rating);
```

### Evitar Condiciones Costosas

```php
// ❌ Evitar condiciones costosas
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

### 1. Usar Índices Apropiados

```php
// ✅ Asegurar que las columnas usadas en where tengan índices
// status, email, created_at, etc.
```

### 2. Validar Entradas

```php
// ✅ Validar entradas antes de usar en where
$status = $request->get('status');
$allowedStatuses = ['active', 'inactive', 'pending'];

if (!in_array($status, $allowedStatuses)) {
    $status = 'active';
}

$users = User::where('status', $status)->get();
```

### 3. Usar Parámetros Preparados

```php
// ✅ Laravel usa parámetros preparados automáticamente
$search = $request->get('search');
$users = User::where('name', 'like', "%{$search}%")->get();
```

### 4. Optimizar Consultas Complejas

```php
// ✅ Usar when para condiciones opcionales
$users = User::when($request->search, function($query, $search) {
    return $query->where('name', 'like', "%{$search}%");
})
->when($request->status, function($query, $status) {
    return $query->where('status', $status);
})
->get();
```

## Debugging de Where

### Ver SQL Generado

```php
// Ver SQL de where
$query = User::where('status', 'active')->where('age', '>', 18);
dd($query->toSql()); // Muestra SQL sin ejecutar

$result = $query->get();
dd($result); // Muestra resultados
```

### Logging de Consultas

```php
// Habilitar logging para debug
DB::enableQueryLog();

$users = User::where('status', 'active')->get();

dd(DB::getQueryLog()); // Muestra todas las consultas ejecutadas
```

---

**Próximo**: [Cláusula OrWhere](./09-clausula-orwhere.md)
