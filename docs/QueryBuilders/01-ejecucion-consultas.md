# 1. Ejecución de Consultas de Base de Datos

## Introducción

Laravel proporciona dos formas principales de ejecutar consultas de base de datos:

1. **Query Builder**: Para consultas SQL complejas y dinámicas
2. **Eloquent ORM**: Para trabajar con modelos y relaciones

## Query Builder

### Consultas Básicas

#### Obtener Todos los Registros

```php
// Query Builder
$users = DB::table('users')->get();

// Eloquent
$users = User::all();
```

#### Obtener un Solo Registro

```php
// Query Builder - por ID
$user = DB::table('users')->find(1);

// Query Builder - primer registro
$user = DB::table('users')->first();

// Query Builder - primer registro con condición
$user = DB::table('users')->where('email', 'admin@example.com')->first();

// Eloquent - por ID
$user = User::find(1);

// Eloquent - primer registro
$user = User::first();

// Eloquent - primer registro con condición
$user = User::where('email', 'admin@example.com')->first();
```

#### Obtener un Valor Específico

```php
// Query Builder - obtener un valor de una columna
$userName = DB::table('users')->where('id', 1)->value('name');

// Eloquent - obtener un valor de una columna
$userName = User::where('id', 1)->value('name');
```

#### Obtener Lista de Valores

```php
// Query Builder - obtener lista de nombres
$names = DB::table('users')->pluck('name');

// Query Builder - obtener lista con clave personalizada
$users = DB::table('users')->pluck('name', 'id');

// Eloquent - obtener lista de nombres
$names = User::pluck('name');

// Eloquent - obtener lista con clave personalizada
$users = User::pluck('name', 'id');
```

## Métodos de Ejecución

### get() - Obtener Colección

```php
// Query Builder
$users = DB::table('users')
    ->where('status', 'active')
    ->orderBy('name')
    ->get();

// Eloquent
$users = User::where('status', 'active')
    ->orderBy('name')
    ->get();
```

### first() - Primer Registro

```php
// Query Builder
$user = DB::table('users')
    ->where('email', 'admin@example.com')
    ->first();

// Eloquent
$user = User::where('email', 'admin@example.com')->first();
```

### find() - Buscar por ID

```php
// Query Builder
$user = DB::table('users')->find(1);

// Eloquent
$user = User::find(1);

// Eloquent - múltiples IDs
$users = User::find([1, 2, 3]);
```

### findOrFail() - Buscar o Fallar

```php
// Query Builder - manual
$user = DB::table('users')->find(1);
if (!$user) {
    abort(404);
}

// Eloquent - automático
$user = User::findOrFail(1); // Lanza ModelNotFoundException si no existe
```

### firstOrFail() - Primer Registro o Fallar

```php
// Query Builder - manual
$user = DB::table('users')->where('email', 'admin@example.com')->first();
if (!$user) {
    abort(404);
}

// Eloquent - automático
$user = User::where('email', 'admin@example.com')->firstOrFail();
```

## Consultas con Condiciones

### Consultas Simples

```php
// Query Builder
$activeUsers = DB::table('users')
    ->where('status', 'active')
    ->get();

// Eloquent
$activeUsers = User::where('status', 'active')->get();
```

### Consultas Múltiples

```php
// Query Builder
$users = DB::table('users')
    ->where('status', 'active')
    ->where('created_at', '>=', now()->subDays(30))
    ->get();

// Eloquent
$users = User::where('status', 'active')
    ->where('created_at', '>=', now()->subDays(30))
    ->get();
```

### Consultas con Ordenamiento

```php
// Query Builder
$users = DB::table('users')
    ->where('status', 'active')
    ->orderBy('name', 'asc')
    ->orderBy('created_at', 'desc')
    ->get();

// Eloquent
$users = User::where('status', 'active')
    ->orderBy('name', 'asc')
    ->orderBy('created_at', 'desc')
    ->get();
```

## Consultas con Relaciones

### Cargar Relaciones (Eager Loading)

```php
// Eloquent - cargar relaciones
$users = User::with('posts')->get();

// Eloquent - cargar múltiples relaciones
$users = User::with(['posts', 'comments'])->get();

// Eloquent - cargar relaciones anidadas
$users = User::with('posts.comments')->get();
```

### Consultas con Relaciones

```php
// Eloquent - consultar con relación
$users = User::whereHas('posts', function($query) {
    $query->where('status', 'published');
})->get();

// Eloquent - consultar sin relación
$users = User::whereDoesntHave('posts')->get();

// Eloquent - contar relaciones
$users = User::withCount('posts')->get();
```

## Consultas Dinámicas

### Consultas Condicionales

```php
// Query Builder
$query = DB::table('users');

if ($request->has('status')) {
    $query->where('status', $request->status);
}

if ($request->has('search')) {
    $query->where('name', 'like', '%' . $request->search . '%');
}

$users = $query->get();

// Eloquent
$query = User::query();

if ($request->has('status')) {
    $query->where('status', $request->status);
}

if ($request->has('search')) {
    $query->where('name', 'like', '%' . $request->search . '%');
}

$users = $query->get();
```

## Optimización de Consultas

### Seleccionar Columnas Específicas

```php
// Query Builder
$users = DB::table('users')
    ->select('id', 'name', 'email')
    ->get();

// Eloquent
$users = User::select('id', 'name', 'email')->get();
```

### Consultas con Índices

```php
// Query Builder - forzar índice
$users = DB::table('users')
    ->from(DB::raw('users FORCE INDEX (status_index)'))
    ->where('status', 'active')
    ->get();

// Eloquent - usar consulta raw
$users = User::from(DB::raw('users FORCE INDEX (status_index)'))
    ->where('status', 'active')
    ->get();
```

## Ejemplos Prácticos

### Dashboard de Usuario

```php
// Obtener estadísticas del usuario
$userStats = DB::table('users')
    ->selectRaw('
        COUNT(*) as total_users,
        COUNT(CASE WHEN status = "active" THEN 1 END) as active_users,
        COUNT(CASE WHEN created_at >= ? THEN 1 END) as new_users
    ', [now()->subDays(30)])
    ->first();

// Equivalente con Eloquent
$userStats = User::selectRaw('
    COUNT(*) as total_users,
    COUNT(CASE WHEN status = "active" THEN 1 END) as active_users,
    COUNT(CASE WHEN created_at >= ? THEN 1 END) as new_users
', [now()->subDays(30)])->first();
```

### Búsqueda Avanzada

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

    // Filtro por fecha
    if ($request->filled('date_from')) {
        $query->where('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->where('created_at', '<=', $request->date_to);
    }

    // Ordenamiento
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    return $query->paginate(15);
}
```

## Mejores Prácticas

### 1. Usar Eloquent para Operaciones CRUD Simples

```php
// ✅ Recomendado
$user = User::find(1);
$user->update(['name' => 'Nuevo Nombre']);

// ❌ Evitar Query Builder para operaciones simples
DB::table('users')->where('id', 1)->update(['name' => 'Nuevo Nombre']);
```

### 2. Usar Query Builder para Consultas Complejas

```php
// ✅ Recomendado para consultas complejas
$stats = DB::table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->selectRaw('users.name, COUNT(posts.id) as post_count')
    ->groupBy('users.id', 'users.name')
    ->having('post_count', '>', 5)
    ->get();
```

### 3. Evitar N+1 Problemas

```php
// ❌ Problema N+1
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->count(); // Consulta adicional por cada usuario
}

// ✅ Solución con eager loading
$users = User::withCount('posts')->get();
foreach ($users as $user) {
    echo $user->posts_count; // Sin consultas adicionales
}
```

### 4. Usar Consultas Preparadas

```php
// ✅ Consultas preparadas automáticas
$users = User::where('status', $status)->get();

// ✅ Para consultas raw complejas
$users = DB::select('SELECT * FROM users WHERE status = ? AND created_at >= ?',
    [$status, $date]);
```

## Debugging de Consultas

### Ver SQL Generado

```php
// Query Builder
$query = DB::table('users')->where('status', 'active');
dd($query->toSql()); // Muestra SQL sin ejecutar
dd($query->get()); // Ejecuta y muestra resultados

// Eloquent
$query = User::where('status', 'active');
dd($query->toSql()); // Muestra SQL sin ejecutar
dd($query->get()); // Ejecuta y muestra resultados
```

### Logging de Consultas

```php
// Habilitar logging de consultas
DB::enableQueryLog();

$users = User::where('status', 'active')->get();

dd(DB::getQueryLog()); // Muestra todas las consultas ejecutadas
```

---

**Próximo**: [Fragmentación](./02-fragmentacion.md)
