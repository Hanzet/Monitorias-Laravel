# 5. Cláusula de Selección

## Introducción

La cláusula SELECT permite especificar qué columnas se deben recuperar de la base de datos. Laravel proporciona métodos flexibles para controlar la selección de datos.

## Selección Básica

### select() - Seleccionar Columnas Específicas

```php
// Query Builder - seleccionar columnas específicas
$users = DB::table('users')
    ->select('id', 'name', 'email')
    ->get();

// Eloquent - seleccionar columnas específicas
$users = User::select('id', 'name', 'email')->get();

// Query Builder - seleccionar todas las columnas (equivalente a *)
$users = DB::table('users')->select('*')->get();

// Eloquent - seleccionar todas las columnas (por defecto)
$users = User::all(); // Equivalente a select('*')
```

### select() con Array

```php
// Query Builder - usar array para columnas
$columns = ['id', 'name', 'email', 'created_at'];
$users = DB::table('users')->select($columns)->get();

// Eloquent - usar array para columnas
$columns = ['id', 'name', 'email', 'created_at'];
$users = User::select($columns)->get();
```

## Aliases y Expresiones

### selectRaw() - Expresiones SQL Crudas

```php
// Query Builder - expresiones SQL crudas
$users = DB::table('users')
    ->selectRaw('id, name, email, DATE_FORMAT(created_at, "%Y-%m-%d") as created_date')
    ->get();

// Eloquent - expresiones SQL crudas
$users = User::selectRaw('id, name, email, DATE_FORMAT(created_at, "%Y-%m-%d") as created_date')
    ->get();
```

### selectRaw() con Parámetros

```php
// Query Builder - con parámetros
$users = DB::table('users')
    ->selectRaw('id, name, email, DATE_FORMAT(created_at, ?) as created_date', ['%Y-%m-%d'])
    ->get();

// Eloquent - con parámetros
$users = User::selectRaw('id, name, email, DATE_FORMAT(created_at, ?) as created_date', ['%Y-%m-%d'])
    ->get();
```

### addSelect() - Agregar Columnas

```php
// Query Builder - agregar columnas a la selección
$users = DB::table('users')
    ->select('id', 'name')
    ->addSelect('email', 'created_at')
    ->get();

// Eloquent - agregar columnas a la selección
$users = User::select('id', 'name')
    ->addSelect('email', 'created_at')
    ->get();
```

### addSelect() con Expresiones

```php
// Query Builder - agregar expresiones
$users = DB::table('users')
    ->select('id', 'name')
    ->addSelect(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'))
    ->get();

// Eloquent - agregar expresiones
$users = User::select('id', 'name')
    ->addSelect(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'))
    ->get();
```

## Subconsultas en SELECT

### Subconsultas Básicas

```php
// Query Builder - subconsulta en SELECT
$users = DB::table('users')
    ->select('users.*', DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count'))
    ->get();

// Eloquent - subconsulta en SELECT
$users = User::select('*', DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count'))
    ->get();
```

### Subconsultas con Condiciones

```php
// Query Builder - subconsulta con condición
$users = DB::table('users')
    ->select('users.*',
        DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id AND posts.status = "published") as published_posts')
    )
    ->get();

// Eloquent - subconsulta con condición
$users = User::select('*',
    DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id AND posts.status = "published") as published_posts')
)->get();
```

### Subconsultas con Agregados

```php
// Query Builder - subconsulta con agregado
$users = DB::table('users')
    ->select('users.*',
        DB::raw('(SELECT SUM(views_count) FROM posts WHERE posts.user_id = users.id) as total_views')
    )
    ->get();

// Eloquent - subconsulta con agregado
$users = User::select('*',
    DB::raw('(SELECT SUM(views_count) FROM posts WHERE posts.user_id = users.id) as total_views')
)->get();
```

## Expresiones Condicionales

### CASE WHEN en SELECT

```php
// Query Builder - CASE WHEN
$users = DB::table('users')
    ->select('users.*',
        DB::raw('CASE
            WHEN status = "active" THEN "Activo"
            WHEN status = "inactive" THEN "Inactivo"
            ELSE "Desconocido"
        END as status_label')
    )
    ->get();

// Eloquent - CASE WHEN
$users = User::select('*',
    DB::raw('CASE
        WHEN status = "active" THEN "Activo"
        WHEN status = "inactive" THEN "Inactivo"
        ELSE "Desconocido"
    END as status_label')
)->get();
```

### Expresiones con Funciones de Fecha

```php
// Query Builder - funciones de fecha
$users = DB::table('users')
    ->select('users.*',
        DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as created_date'),
        DB::raw('DATEDIFF(NOW(), created_at) as days_since_created')
    )
    ->get();

// Eloquent - funciones de fecha
$users = User::select('*',
    DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as created_date'),
    DB::raw('DATEDIFF(NOW(), created_at) as days_since_created')
)->get();
```

## Selección con Joins

### Selección en Joins

```php
// Query Builder - selección con join
$posts = DB::table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name', 'users.email as author_email')
    ->get();

// Eloquent - selección con join
$posts = Post::join('users', 'posts.user_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name', 'users.email as author_email')
    ->get();
```

### Selección Múltiple en Joins

```php
// Query Builder - múltiples joins con selección
$comments = DB::table('comments')
    ->join('users', 'comments.user_id', '=', 'users.id')
    ->join('posts', 'comments.post_id', '=', 'posts.id')
    ->select(
        'comments.*',
        'users.name as commenter_name',
        'posts.title as post_title'
    )
    ->get();

// Eloquent - múltiples joins con selección
$comments = Comment::join('users', 'comments.user_id', '=', 'users.id')
    ->join('posts', 'comments.post_id', '=', 'posts.id')
    ->select(
        'comments.*',
        'users.name as commenter_name',
        'posts.title as post_title'
    )
    ->get();
```

## Selección con Relaciones

### select() con Relaciones

```php
// Eloquent - selección con relaciones
$users = User::with(['posts' => function($query) {
    $query->select('id', 'user_id', 'title', 'status');
}])->select('id', 'name', 'email')->get();

// Eloquent - selección específica en relaciones
$users = User::with(['posts' => function($query) {
    $query->select('id', 'user_id', 'title', 'status')
          ->where('status', 'published');
}])->get();
```

### select() con Relaciones Anidadas

```php
// Eloquent - relaciones anidadas con selección
$users = User::with([
    'posts' => function($query) {
        $query->select('id', 'user_id', 'title', 'status');
    },
    'posts.comments' => function($query) {
        $query->select('id', 'post_id', 'content', 'rating');
    }
])->select('id', 'name', 'email')->get();
```

## Ejemplos Prácticos

### Dashboard de Usuario

```php
public function getUserDashboard($userId)
{
    $user = User::select('id', 'name', 'email', 'status', 'created_at')
        ->addSelect(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as joined_date'))
        ->addSelect(DB::raw('DATEDIFF(NOW(), created_at) as days_member'))
        ->withCount(['posts', 'comments'])
        ->withSum('posts', 'views_count')
        ->findOrFail($userId);

    return $user;
}
```

### Reporte de Posts

```php
public function getPostsReport()
{
    return Post::select('id', 'title', 'status', 'views_count', 'user_id', 'created_at')
        ->addSelect(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'))
        ->addSelect(DB::raw('(SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) as comment_count'))
        ->addSelect(DB::raw('(SELECT AVG(rating) FROM comments WHERE comments.post_id = posts.id) as average_rating'))
        ->with('user:id,name')
        ->get();
}
```

### Estadísticas de Usuario

```php
public function getUserStats($userId)
{
    return User::select('id', 'name', 'email')
        ->addSelect(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as total_posts'))
        ->addSelect(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id AND posts.status = "published") as published_posts'))
        ->addSelect(DB::raw('(SELECT SUM(views_count) FROM posts WHERE posts.user_id = users.id) as total_views'))
        ->addSelect(DB::raw('(SELECT AVG(views_count) FROM posts WHERE posts.user_id = users.id) as average_views'))
        ->addSelect(DB::raw('(SELECT COUNT(*) FROM comments WHERE comments.user_id = users.id) as total_comments'))
        ->addSelect(DB::raw('(SELECT AVG(rating) FROM comments WHERE comments.user_id = users.id) as average_rating'))
        ->where('id', $userId)
        ->first();
}
```

### Lista de Usuarios con Actividad

```php
public function getActiveUsers()
{
    return User::select('id', 'name', 'email', 'status', 'created_at')
        ->addSelect(DB::raw('CASE
            WHEN status = "active" THEN "Activo"
            WHEN status = "inactive" THEN "Inactivo"
            ELSE "Desconocido"
        END as status_label'))
        ->addSelect(DB::raw('(SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count'))
        ->addSelect(DB::raw('(SELECT MAX(created_at) FROM posts WHERE posts.user_id = users.id) as last_post_date'))
        ->having('post_count', '>', 0)
        ->orderBy('post_count', 'desc')
        ->get();
}
```

## Optimización de Selección

### Seleccionar Solo lo Necesario

```php
// ❌ Seleccionar todo cuando no es necesario
$users = User::all(); // Carga todas las columnas

// ✅ Seleccionar solo las columnas necesarias
$users = User::select('id', 'name', 'email')->get();
```

### Usar Índices Apropiados

```php
// ✅ Asegurar que las columnas seleccionadas tengan índices
// id, name, email, status, created_at, etc.
```

### Evitar SELECT \* en Producción

```php
// ❌ Evitar en producción
$users = User::select('*')->get();

// ✅ Especificar columnas
$users = User::select('id', 'name', 'email')->get();
```

## Mejores Prácticas

### 1. Seleccionar Columnas Específicas

```php
// ✅ Siempre especificar las columnas necesarias
$users = User::select('id', 'name', 'email')->get();
```

### 2. Usar Aliases para Claridad

```php
// ✅ Usar aliases descriptivos
$users = User::select('id', 'name', 'email')
    ->addSelect(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as registration_date'))
    ->get();
```

### 3. Optimizar Subconsultas

```php
// ✅ Usar joins en lugar de subconsultas cuando sea posible
$users = User::join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', DB::raw('COUNT(posts.id) as post_count'))
    ->groupBy('users.id')
    ->get();
```

### 4. Usar Expresiones Condicionales

```php
// ✅ Usar CASE WHEN para lógica condicional
$users = User::select('*',
    DB::raw('CASE
        WHEN status = "active" THEN "Activo"
        ELSE "Inactivo"
    END as status_label')
)->get();
```

## Debugging de Selección

### Ver SQL Generado

```php
// Ver SQL de selección
$query = User::select('id', 'name', 'email');
dd($query->toSql()); // Muestra SQL sin ejecutar

$result = $query->get();
dd($result); // Muestra resultados
```

### Verificar Columnas Seleccionadas

```php
// Verificar qué columnas se están seleccionando
$user = User::select('id', 'name', 'email')->first();
dd($user->getAttributes()); // Muestra solo las columnas seleccionadas
```

---

**Próximo**: [Expresiones Sin Procesar](./06-expresiones-sin-procesar.md)
