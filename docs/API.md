# Documentación de la API

## 🌐 Información General

-   **Base URL**: `http://localhost:8000/api`
-   **Versión**: v1
-   **Formato**: JSON
-   **Autenticación**: Bearer Token (Laravel Sanctum)

## 🔐 Autenticación

La API utiliza Laravel Sanctum para la autenticación. Para acceder a rutas protegidas, incluye el token en el header:

```
Authorization: Bearer {token}
```

### Obtener Token

1. **Registro**: `POST /api/register`
2. **Login**: `POST /api/login`

## 📋 Endpoints

### 🔓 Rutas Públicas

#### 1. Registro de Usuario

**POST** `/api/register`

Registra un nuevo usuario en el sistema.

**Headers:**

```
Content-Type: application/json
Accept: application/json
```

**Body:**

```json
{
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Respuesta Exitosa (201):**

```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "email_verified_at": null,
        "created_at": "2025-07-29T20:00:00.000000Z",
        "updated_at": "2025-07-29T20:00:00.000000Z"
    },
    "token": "1|abc123def456ghi789...",
    "status": 201
}
```

**Respuesta de Error (422):**

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password confirmation does not match."]
    }
}
```

#### 2. Login de Usuario

**POST** `/api/login`

Autentica un usuario existente.

**Headers:**

```
Content-Type: application/json
Accept: application/json
```

**Body:**

```json
{
    "email": "juan@example.com",
    "password": "password123"
}
```

**Respuesta Exitosa (200):**

```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "email_verified_at": null,
        "created_at": "2025-07-29T20:00:00.000000Z",
        "updated_at": "2025-07-29T20:00:00.000000Z"
    },
    "token": "2|xyz789abc123def456...",
    "status": 200
}
```

**Respuesta de Error (401):**

```json
{
    "message": "Invalid credentials",
    "status": 401
}
```

#### 3. Test de API

**GET** `/api/test`

Verifica que la API esté funcionando correctamente.

**Headers:**

```
Accept: application/json
```

**Respuesta Exitosa (200):**

```json
{
    "message": "API is working!",
    "status": 200
}
```

### 🔒 Rutas Protegidas

> **Nota**: Todas las rutas protegidas requieren el header de autenticación:
>
> ```
> Authorization: Bearer {token}
> ```

#### 4. Información del Usuario Autenticado

**GET** `/api/user`

Obtiene la información del usuario autenticado.

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**

```json
{
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "email_verified_at": null,
        "created_at": "2025-07-29T20:00:00.000000Z",
        "updated_at": "2025-07-29T20:00:00.000000Z"
    },
    "status": 200
}
```

**Respuesta de Error (401):**

```json
{
    "message": "Unauthenticated",
    "status": 401
}
```

#### 5. Logout

**POST** `/api/logout`

Cierra la sesión del usuario autenticado.

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**

```json
{
    "message": "Logged out successfully",
    "status": 200
}
```

### 👥 Gestión de Usuarios

#### 6. Listar Todos los Usuarios

**GET** `/api/users`

Obtiene una lista de todos los usuarios.

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**

```json
{
    "users": [
        {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "email_verified_at": null,
            "created_at": "2025-07-29T20:00:00.000000Z",
            "updated_at": "2025-07-29T20:00:00.000000Z"
        },
        {
            "id": 2,
            "name": "María García",
            "email": "maria@example.com",
            "email_verified_at": null,
            "created_at": "2025-07-29T21:00:00.000000Z",
            "updated_at": "2025-07-29T21:00:00.000000Z"
        }
    ],
    "status": 200
}
```

#### 7. Obtener Usuario Específico

**GET** `/api/users/{id}`

Obtiene la información de un usuario específico.

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**

```json
{
    "user": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "email_verified_at": null,
        "created_at": "2025-07-29T20:00:00.000000Z",
        "updated_at": "2025-07-29T20:00:00.000000Z"
    },
    "status": 200
}
```

**Respuesta de Error (404):**

```json
{
    "message": "No query results for model [App\\Models\\User] 1",
    "status": 404
}
```

#### 8. Actualizar Usuario

**PUT/PATCH** `/api/users/{id}`

Actualiza la información de un usuario.

**Headers:**

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body:**

```json
{
    "name": "Juan Carlos Pérez",
    "email": "juancarlos@example.com"
}
```

**Respuesta Exitosa (200):**

```json
{
    "message": "User updated successfully",
    "user": {
        "id": 1,
        "name": "Juan Carlos Pérez",
        "email": "juancarlos@example.com",
        "email_verified_at": null,
        "created_at": "2025-07-29T20:00:00.000000Z",
        "updated_at": "2025-07-29T22:00:00.000000Z"
    },
    "status": 200
}
```

#### 9. Eliminar Usuario

**DELETE** `/api/users/{id}`

Elimina un usuario del sistema.

**Headers:**

```
Authorization: Bearer {token}
Accept: application/json
```

**Respuesta Exitosa (200):**

```json
{
    "message": "User deleted successfully",
    "status": 200
}
```

## 📊 Códigos de Estado HTTP

| Código | Descripción           | Uso                         |
| ------ | --------------------- | --------------------------- |
| 200    | OK                    | Respuesta exitosa           |
| 201    | Created               | Recurso creado exitosamente |
| 401    | Unauthorized          | No autenticado              |
| 404    | Not Found             | Recurso no encontrado       |
| 422    | Unprocessable Entity  | Error de validación         |
| 500    | Internal Server Error | Error del servidor          |

## 🔍 Ejemplos de Uso

### Ejemplo 1: Registro y Login

```bash
# 1. Registrar usuario
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

### Ejemplo 2: Acceso a Rutas Protegidas

```bash
# Obtener información del usuario
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Listar todos los usuarios
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Ejemplo 3: Gestión de Usuarios

```bash
# Actualizar usuario
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Name",
    "email": "updated@example.com"
  }'

# Eliminar usuario
curl -X DELETE http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

## 🧪 Testing con Postman

### Colección de Postman

Puedes importar la colección de Postman incluida en el proyecto:

**Archivo**: `postman_collection.json`

### Variables de Entorno en Postman

1. **Base URL**: `http://localhost:8000/api`
2. **Token**: Variable que se actualiza automáticamente después del login

### Flujo de Testing

1. **Registro**: Ejecutar `POST /register`
2. **Login**: Ejecutar `POST /login` (guarda el token automáticamente)
3. **Rutas Protegidas**: Usar el token para acceder a rutas protegidas

## 🔧 Configuración de CORS

Si necesitas acceder desde un frontend, configura CORS en `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

## 📝 Validaciones

### Registro

-   `name`: Requerido, máximo 255 caracteres
-   `email`: Requerido, email válido, único en la tabla users
-   `password`: Requerido, mínimo 6 caracteres, debe coincidir con confirmación

### Login

-   `email`: Requerido, email válido
-   `password`: Requerido, mínimo 6 caracteres

### Actualización de Usuario

-   `name`: Opcional, máximo 255 caracteres
-   `email`: Opcional, email válido, único en la tabla users

## 🚨 Manejo de Errores

### Errores de Validación (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Error message"]
    }
}
```

### Errores de Autenticación (401)

```json
{
    "message": "Unauthenticated",
    "status": 401
}
```

### Errores de Recurso No Encontrado (404)

```json
{
    "message": "No query results for model [App\\Models\\User] {id}",
    "status": 404
}
```

## 🔄 Rate Limiting

La API incluye rate limiting por defecto. Los límites se pueden configurar en `app/Http/Kernel.php`:

```php
'api' => [
    \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    \Illuminate\Http\Middleware\SubstituteBindings::class,
],
```

## 📈 Monitoreo

### Logs de API

Los logs de la API se guardan en `storage/logs/laravel.log`. Para monitorear en tiempo real:

```bash
tail -f storage/logs/laravel.log
```

### Telescope (Opcional)

Laravel Telescope está configurado para debugging. Accede a:
`http://localhost:8000/telescope`

## 🔐 Seguridad

### Tokens de Acceso

-   Los tokens se generan usando Laravel Sanctum
-   Los tokens no expiran por defecto
-   Se pueden revocar individualmente
-   Se almacenan de forma segura en la base de datos

### Headers de Seguridad

La API incluye headers de seguridad automáticos:

-   `X-Frame-Options`
-   `X-Content-Type-Options`
-   `X-XSS-Protection`

## 📚 Recursos Adicionales

-   [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
-   [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
-   [Postman Documentation](https://learning.postman.com/)
-   [HTTP Status Codes](https://httpstatuses.com/)
