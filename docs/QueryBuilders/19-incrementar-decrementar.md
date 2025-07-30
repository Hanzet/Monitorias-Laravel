# Incrementar y Decrementar en Laravel

Laravel facilita la actualización de valores numéricos en la base de datos usando los métodos `increment()` y `decrement()` tanto en Eloquent como en Query Builder.

## Incrementar Valores

### 1. Incrementar un Campo

```php
// Incrementar el contador de vistas de un post
Post::find(1)->increment('views_count');

// Incrementar en una cantidad específica
User::find(1)->increment('login_count', 5);
```

### 2. Incrementar con Condición

```php
// Incrementar solo usuarios activos
User::where('status', 'active')->increment('points', 10);
```

### 3. Incrementar y Actualizar Otros Campos

```php
// Incrementar y actualizar otro campo
Post::find(1)->increment('views_count', 1, ['last_viewed_at' => now()]);
```

## Decrementar Valores

### 1. Decrementar un Campo

```php
// Decrementar el stock de un producto
Product::find(1)->decrement('stock');

// Decrementar en una cantidad específica
User::find(1)->decrement('points', 3);
```

### 2. Decrementar con Condición

```php
// Decrementar solo productos activos
Product::where('active', true)->decrement('stock', 2);
```

### 3. Decrementar y Actualizar Otros Campos

```php
// Decrementar y actualizar otro campo
User::find(1)->decrement('points', 1, ['last_activity' => now()]);
```

## Uso con Query Builder

```php
// Incrementar con Query Builder
DB::table('posts')->where('id', 1)->increment('views_count');

// Decrementar con Query Builder
DB::table('products')->where('id', 1)->decrement('stock', 5);
```

## Uso con Relaciones

```php
// Incrementar el contador de comentarios de un post
$post = Post::find(1);
$post->comments()->increment('likes_count');
```

## Casos de Uso Comunes

### 1. Contadores de Vistas

```php
// Incrementar vistas cada vez que se muestra un post
Post::find($id)->increment('views_count');
```

### 2. Puntos de Usuario

```php
// Sumar puntos por actividad
User::find($id)->increment('points', 10);
```

### 3. Stock de Productos

```php
// Reducir stock al realizar una venta
Product::find($id)->decrement('stock', $cantidad);
```

## Mejores Prácticas

-   Usar increment/decrement para evitar condiciones de carrera.
-   Combinar con condiciones para actualizar solo los registros deseados.
-   Usar transacciones si se actualizan varios registros relacionados.
-   Validar los valores antes de incrementar/decrementar.

## Ejercicios Prácticos

### Ejercicio 1: Incrementar Contador

```php
// Incrementar el contador de logins de un usuario
User::find($id)->increment('login_count');
```

### Ejercicio 2: Decrementar Stock

```php
// Decrementar el stock de un producto al vender
Product::find($id)->decrement('stock', $cantidad);
```

### Ejercicio 3: Incrementar y Actualizar

```php
// Incrementar puntos y actualizar fecha de actividad
User::find($id)->increment('points', 5, ['last_activity' => now()]);
```

## Resumen

Incrementar y decrementar valores en Laravel es eficiente y seguro usando los métodos dedicados. Siempre valida los datos y usa las mejores prácticas para mantener la integridad de la base de datos.
