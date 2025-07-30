# Límite y Compensación de Registros en Laravel

En Laravel, puedes limitar la cantidad de registros devueltos y saltar un número específico de registros usando los métodos `limit()` y `offset()`. Esto es útil para paginación, reportes y optimización de consultas.

## Conceptos Básicos

### 1. Limit

```php
// Obtener solo 10 usuarios
$users = User::limit(10)->get();

// Obtener los 5 posts más recientes
$posts = Post::orderBy('created_at', 'desc')->limit(5)->get();
```

### 2. Offset

```php
// Saltar los primeros 10 usuarios y obtener los siguientes 10
$users = User::offset(10)->limit(10)->get();

// Saltar los primeros 5 posts y obtener los siguientes 5
$posts = Post::orderBy('created_at', 'desc')->offset(5)->limit(5)->get();
```

## Uso Combinado: Limit y Offset

```php
// Paginación manual: página 3, 10 registros por página
$page = 3;
$perPage = 10;
$users = User::offset(($page - 1) * $perPage)->limit($perPage)->get();
```

## Uso con Eloquent y Query Builder

```php
// Eloquent
$users = User::limit(5)->offset(10)->get();

// Query Builder
$posts = DB::table('posts')->limit(20)->offset(40)->get();
```

## Uso con OrderBy

```php
// Siempre combinar limit/offset con orderBy para resultados consistentes
$users = User::orderBy('created_at', 'desc')->limit(10)->offset(20)->get();
```

## Uso con Relaciones

```php
// Obtener los 3 posts más recientes de cada usuario
$users = User::with(['posts' => function($query) {
    $query->orderBy('created_at', 'desc')->limit(3);
}])->get();
```

## Uso con Paginación

Laravel provee métodos de paginación que internamente usan limit y offset:

```php
// Paginación automática
$users = User::paginate(15); // 15 por página
$posts = Post::simplePaginate(10); // 10 por página, sin contar total
```

## Casos de Uso Comunes

### 1. Reportes Parciales

```php
// Obtener los primeros 100 registros para un reporte
$users = User::limit(100)->get();
```

### 2. Scroll Infinito

```php
// Obtener más registros al hacer scroll
$lastId = $request->get('last_id');
$posts = Post::where('id', '>', $lastId)->limit(10)->get();
```

### 3. Exportación por Lotes

```php
// Exportar datos en lotes de 500
$offset = 0;
do {
    $batch = User::offset($offset)->limit(500)->get();
    // Procesar $batch
    $offset += 500;
} while ($batch->count() > 0);
```

## Mejores Prácticas

-   Siempre usar `orderBy` junto con `limit` y `offset` para evitar resultados inconsistentes.
-   Para paginación, preferir los métodos `paginate()` y `simplePaginate()` de Eloquent.
-   Evitar usar `offset` en tablas muy grandes, ya que puede afectar el rendimiento.
-   Para grandes volúmenes, considerar el uso de `where('id', '>', $lastId)` para paginación eficiente.

## Ejercicios Prácticos

### Ejercicio 1: Paginación Manual

```php
// Obtener la página 2 de usuarios, 20 por página
$page = 2;
$perPage = 20;
$users = User::offset(($page - 1) * $perPage)->limit($perPage)->get();
```

### Ejercicio 2: Top N Registros

```php
// Obtener los 5 posts con más comentarios
$posts = Post::withCount('comments')
    ->orderBy('comments_count', 'desc')
    ->limit(5)
    ->get();
```

### Ejercicio 3: Scroll Infinito

```php
// Obtener los siguientes 10 usuarios después del último ID mostrado
$lastId = $request->get('last_id');
$users = User::where('id', '>', $lastId)
    ->orderBy('id', 'asc')
    ->limit(10)
    ->get();
```

## Resumen

El uso de `limit` y `offset` en Laravel permite controlar la cantidad de registros devueltos y facilita la implementación de paginación, reportes y procesamiento por lotes de manera eficiente.
