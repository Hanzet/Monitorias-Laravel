# Insertar Registros en Laravel

En Laravel, puedes insertar registros en la base de datos usando Eloquent o el Query Builder. Aquí se muestran ambos enfoques y sus mejores prácticas.

## Insertar con Eloquent

### 1. Crear y Guardar un Modelo

```php
// Crear un nuevo usuario
$user = new User();
$user->name = 'Juan';
$user->email = 'juan@example.com';
$user->password = bcrypt('secreto');
$user->save();
```

### 2. Crear con create()

```php
// Usar asignación masiva
$user = User::create([
    'name' => 'Ana',
    'email' => 'ana@example.com',
    'password' => bcrypt('secreto'),
]);
```

### 3. Insertar Múltiples Registros

```php
// Insertar varios usuarios a la vez
$users = [
    ['name' => 'Pedro', 'email' => 'pedro@example.com', 'password' => bcrypt('secreto')],
    ['name' => 'Lucía', 'email' => 'lucia@example.com', 'password' => bcrypt('secreto')],
];
User::insert($users);
```

## Insertar con Query Builder

### 1. Insertar un Registro

```php
DB::table('users')->insert([
    'name' => 'Carlos',
    'email' => 'carlos@example.com',
    'password' => bcrypt('secreto'),
]);
```

### 2. Insertar y Obtener el ID

```php
$id = DB::table('users')->insertGetId([
    'name' => 'Laura',
    'email' => 'laura@example.com',
    'password' => bcrypt('secreto'),
]);
```

### 3. Insertar Múltiples Registros

```php
DB::table('users')->insert([
    ['name' => 'Mario', 'email' => 'mario@example.com', 'password' => bcrypt('secreto')],
    ['name' => 'Sofía', 'email' => 'sofia@example.com', 'password' => bcrypt('secreto')],
]);
```

## Insertar con Relaciones

```php
// Crear un post para un usuario
$user = User::find(1);
$post = $user->posts()->create([
    'title' => 'Nuevo post',
    'content' => 'Contenido del post',
]);
```

## Insertar con Transacciones

```php
DB::transaction(function () {
    User::create([
        'name' => 'Transacción',
        'email' => 'transaccion@example.com',
        'password' => bcrypt('secreto'),
    ]);
    // Otras operaciones...
});
```

## Insertar con Factory y Seeder

```php
// Usar factory para pruebas o seeders
User::factory()->count(10)->create();
```

## Casos de Uso Comunes

### 1. Registro de Usuarios

```php
// Registrar un usuario desde un formulario
$user = User::create($request->only(['name', 'email', 'password']));
```

### 2. Importación Masiva

```php
// Importar usuarios desde un archivo CSV
foreach ($csvRows as $row) {
    User::create($row);
}
```

### 3. Insertar con Relaciones

```php
// Crear un comentario para un post
$post = Post::find(1);
$comment = $post->comments()->create([
    'user_id' => 2,
    'content' => '¡Buen post!'
]);
```

## Mejores Prácticas

-   Usar asignación masiva (`create`) solo con campos permitidos en `$fillable`.
-   Validar los datos antes de insertar.
-   Usar transacciones para operaciones críticas.
-   Usar factories y seeders para pruebas y datos de ejemplo.
-   Evitar insertar datos sensibles sin cifrar (ej: contraseñas).

## Ejercicios Prácticos

### Ejercicio 1: Registro Básico

```php
// Crear un usuario con datos de un formulario
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => bcrypt($request->password),
]);
```

### Ejercicio 2: Insertar en Transacción

```php
// Insertar usuario y post en una transacción
DB::transaction(function () use ($request) {
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);
    $user->posts()->create([
        'title' => $request->title,
        'content' => $request->content,
    ]);
});
```

### Ejercicio 3: Importación Masiva

```php
// Insertar múltiples usuarios desde un array
$users = [
    ['name' => 'A', 'email' => 'a@example.com', 'password' => bcrypt('a')],
    ['name' => 'B', 'email' => 'b@example.com', 'password' => bcrypt('b')],
];
User::insert($users);
```

## Resumen

Insertar registros en Laravel es sencillo y seguro usando Eloquent o Query Builder. Siempre valida los datos y usa las mejores prácticas para mantener la integridad y seguridad de la base de datos.
