# Paginación en Laravel

Laravel facilita la paginación de resultados usando los métodos `paginate()` y `simplePaginate()` tanto en Eloquent como en Query Builder.

## Conceptos Básicos

### 1. Paginate

```php
// Paginación estándar (con conteo total)
$users = User::paginate(15); // 15 por página
$posts = Post::paginate(10); // 10 por página
```

### 2. SimplePaginate

```php
// Paginación simple (sin conteo total, más eficiente)
$users = User::simplePaginate(20);
```

## Uso con Query Builder

```php
// Paginación con Query Builder
$posts = DB::table('posts')->paginate(10);
```

## Personalización de Paginación

```php
// Cambiar el nombre del parámetro de página
$users = User::paginate(10, ['*'], 'pagina');

// Obtener página específica
$page = 3;
$users = User::paginate(10, ['*'], 'page', $page);
```

## Uso con Filtros y Ordenación

```php
// Paginación con filtros y orden
$search = $request->search;
$users = User::when($search, function($query, $search) {
    return $query->where('name', 'like', "%{$search}%");
})->orderBy('created_at', 'desc')->paginate(15);
```

## Uso con Relaciones

```php
// Paginación de posts con comentarios
$posts = Post::with('comments')->paginate(10);
```

## Respuesta de Paginación

El resultado de `paginate()` incluye:

-   `data`: los registros de la página actual
-   `current_page`, `last_page`, `per_page`, `total`, `next_page_url`, `prev_page_url`, etc.

```php
return response()->json($users);
```

## Paginación Manual con limit y offset

```php
$page = 2;
$perPage = 10;
$users = User::offset(($page - 1) * $perPage)->limit($perPage)->get();
```

## Casos de Uso Comunes

### 1. Listados en Paneles de Administración

```php
// Listar usuarios paginados
$users = User::orderBy('name')->paginate(20);
```

### 2. API con Paginación

```php
// API que retorna posts paginados
return Post::paginate(10);
```

### 3. Scroll Infinito

```php
// Usar simplePaginate para scroll infinito
$posts = Post::orderBy('created_at', 'desc')->simplePaginate(10);
```

## Mejores Prácticas

-   Usar `paginate()` para obtener información completa de la paginación.
-   Usar `simplePaginate()` para grandes volúmenes o scroll infinito.
-   Combinar con filtros y ordenación para resultados relevantes.
-   Personalizar los parámetros de página si es necesario.
-   Validar los parámetros de paginación recibidos del usuario.

## Ejercicios Prácticos

### Ejercicio 1: Paginación Básica

```php
// Paginación de usuarios, 25 por página
$users = User::paginate(25);
```

### Ejercicio 2: Paginación con Filtros

```php
// Paginación de posts filtrados por categoría
$category = $request->category;
$posts = Post::when($category, function($query, $category) {
    return $query->where('category', $category);
})->paginate(10);
```

### Ejercicio 3: API con Paginación

```php
// API que retorna comentarios paginados
return Comment::paginate(15);
```

## Resumen

La paginación en Laravel es sencilla y poderosa, permitiendo manejar grandes volúmenes de datos de manera eficiente y amigable para el usuario.
