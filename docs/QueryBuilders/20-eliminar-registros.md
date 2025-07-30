# Eliminar Registros en Laravel

En Laravel, puedes eliminar registros usando Eloquent o el Query Builder. Aquí se muestran ambos enfoques y sus mejores prácticas.

## Eliminar con Eloquent

### 1. Eliminar un Modelo

```php
$user = User::find(1);
$user->delete();
```

### 2. Eliminar por Condición

```php
User::where('status', 'inactive')->delete();
```

### 3. Eliminar Múltiples Registros

```php
Post::where('created_at', '<', now()->subYear())->delete();
```

## Eliminar con Query Builder

### 1. Eliminar un Registro

```php
DB::table('users')->where('id', 1)->delete();
```

### 2. Eliminar Múltiples Registros

```php
DB::table('posts')->where('status', 'draft')->delete();
```

## Eliminar con Relaciones

```php
// Eliminar todos los comentarios de un post
$post = Post::find(1);
$post->comments()->delete();
```

## Soft Deletes (Eliminación Lógica)

Laravel permite eliminar registros de forma lógica usando Soft Deletes:

```php
// Habilitar SoftDeletes en el modelo
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model {
    use SoftDeletes;
}

// Eliminar lógicamente
$user = User::find(1);
$user->delete(); // No se elimina físicamente, solo se marca como eliminado

// Consultar solo registros no eliminados
$users = User::all();

// Consultar registros eliminados
$deleted = User::onlyTrashed()->get();

// Restaurar un registro eliminado
$user = User::withTrashed()->find(1);
$user->restore();

// Eliminar definitivamente
$user->forceDelete();
```

## Eliminar con Transacciones

```php
DB::transaction(function () {
    User::where('status', 'inactive')->delete();
    // Otras operaciones...
});
```

## Casos de Uso Comunes

### 1. Eliminación Masiva

```php
// Eliminar todos los usuarios inactivos
User::where('status', 'inactive')->delete();
```

### 2. Eliminación Condicional

```php
// Eliminar posts antiguos
Post::where('created_at', '<', now()->subYear())->delete();
```

### 3. Soft Deletes

```php
// Eliminar lógicamente un usuario
User::find($id)->delete();
// Restaurar
User::withTrashed()->find($id)->restore();
```

## Mejores Prácticas

-   Usar SoftDeletes para evitar pérdida de datos.
-   Validar antes de eliminar registros críticos.
-   Usar transacciones para operaciones masivas.
-   Evitar eliminar registros relacionados sin control de integridad.
-   Usar eventos de modelo (`deleting`, `deleted`) para lógica adicional.

## Ejercicios Prácticos

### Ejercicio 1: Eliminación Básica

```php
// Eliminar un usuario por ID
User::find($id)->delete();
```

### Ejercicio 2: Soft Delete y Restaurar

```php
// Eliminar lógicamente y restaurar
User::find($id)->delete();
User::withTrashed()->find($id)->restore();
```

### Ejercicio 3: Eliminación Masiva

```php
// Eliminar todos los posts de un usuario
User::find($id)->posts()->delete();
```

## Resumen

Eliminar registros en Laravel es seguro y flexible usando Eloquent o Query Builder. Prefiere SoftDeletes para evitar pérdida de datos y usa las mejores prácticas para mantener la integridad de la base de datos.
