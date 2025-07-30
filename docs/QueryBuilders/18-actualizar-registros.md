# Actualizar Registros en Laravel

En Laravel, puedes actualizar registros usando Eloquent o el Query Builder. Aquí se muestran ambos enfoques y sus mejores prácticas.

## Actualizar con Eloquent

### 1. Actualizar un Modelo Existente

```php
$user = User::find(1);
$user->name = 'Nuevo Nombre';
$user->save();
```

### 2. Actualizar con update()

```php
User::where('status', 'inactive')->update(['status' => 'active']);
```

### 3. Actualizar con fill()

```php
$user = User::find(2);
$user->fill([
    'name' => 'Nombre Actualizado',
    'email' => 'nuevo@email.com',
]);
$user->save();
```

## Actualizar con Query Builder

### 1. Actualizar un Registro

```php
DB::table('users')->where('id', 1)->update(['name' => 'Nombre Actualizado']);
```

### 2. Actualizar Múltiples Registros

```php
DB::table('posts')->where('status', 'draft')->update(['status' => 'published']);
```

## Actualizar con Relaciones

```php
// Actualizar todos los posts de un usuario
$user = User::find(1);
$user->posts()->update(['status' => 'archived']);
```

## Actualizar con Transacciones

```php
DB::transaction(function () {
    User::where('status', 'pending')->update(['status' => 'active']);
    // Otras operaciones...
});
```

## Actualizar con Modelos y Métodos Especiales

```php
// updateOrCreate: actualiza si existe, crea si no
User::updateOrCreate(
    ['email' => 'test@example.com'],
    ['name' => 'Test', 'status' => 'active']
);

// firstOrCreate: busca y crea si no existe
User::firstOrCreate(
    ['email' => 'nuevo@example.com'],
    ['name' => 'Nuevo', 'status' => 'active']
);
```

## Casos de Uso Comunes

### 1. Actualización Masiva

```php
// Activar todos los usuarios inactivos
User::where('status', 'inactive')->update(['status' => 'active']);
```

### 2. Actualización Condicional

```php
// Cambiar el estado de posts antiguos
Post::where('created_at', '<', now()->subYear())->update(['status' => 'archived']);
```

### 3. Actualizar con Relaciones

```php
// Marcar todos los comentarios de un post como leídos
$post = Post::find(1);
$post->comments()->update(['read' => true]);
```

## Mejores Prácticas

-   Validar los datos antes de actualizar.
-   Usar transacciones para operaciones críticas.
-   Usar métodos masivos (`update`, `updateOrCreate`) para eficiencia.
-   Evitar actualizar campos sensibles sin validación.
-   Usar eventos de modelo (`updating`, `updated`) para lógica adicional.

## Ejercicios Prácticos

### Ejercicio 1: Actualización Básica

```php
// Actualizar el nombre de un usuario
$user = User::find($id);
$user->name = $request->name;
$user->save();
```

### Ejercicio 2: Actualización Masiva

```php
// Cambiar el estado de todos los posts de un usuario
User::find($id)->posts()->update(['status' => 'archived']);
```

### Ejercicio 3: updateOrCreate

```php
// Actualizar o crear un usuario
User::updateOrCreate(
    ['email' => $request->email],
    ['name' => $request->name, 'status' => 'active']
);
```

## Resumen

Actualizar registros en Laravel es eficiente y seguro usando Eloquent o Query Builder. Siempre valida los datos y usa las mejores prácticas para mantener la integridad y seguridad de la base de datos.
