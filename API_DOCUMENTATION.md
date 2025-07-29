# 📚 Documentación de la API de Autenticación

## 🔐 Endpoints de Autenticación

### Base URL

```
http://localhost:8000/api
```

---

## 📝 **1. Registro de Usuario**

### **POST** `/register`

Registra un nuevo usuario en el sistema.

#### **Headers**

```
Content-Type: application/json
Accept: application/json
```

#### **Body**

```json
{
    "name": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### **Respuesta Exitosa (201)**

```json
{
    "success": true,
    "message": "Usuario registrado exitosamente",
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@ejemplo.com",
            "created_at": "2024-01-15T10:30:00.000000Z"
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

#### **Respuesta de Error (422)**

```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "email": ["Este email ya está registrado"],
        "password": ["La confirmación de la contraseña no coincide"]
    }
}
```

---

## 🔑 **2. Inicio de Sesión**

### **POST** `/login`

Inicia sesión con credenciales de usuario.

#### **Headers**

```
Content-Type: application/json
Accept: application/json
```

#### **Body**

```json
{
    "email": "juan@ejemplo.com",
    "password": "password123"
}
```

#### **Respuesta Exitosa (200)**

```json
{
    "success": true,
    "message": "Inicio de sesión exitoso",
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@ejemplo.com",
            "created_at": "2024-01-15T10:30:00.000000Z"
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

#### **Respuesta de Error (401)**

```json
{
    "success": false,
    "message": "Credenciales inválidas"
}
```

---

## 👤 **3. Obtener Información del Usuario**

### **GET** `/me`

Obtiene la información del usuario autenticado.

#### **Headers**

```
Authorization: Bearer {token}
Accept: application/json
```

#### **Respuesta Exitosa (200)**

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@ejemplo.com",
            "email_verified_at": null,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

---

## 🚪 **4. Cerrar Sesión**

### **POST** `/logout`

Cierra la sesión actual del usuario (revoca el token actual).

#### **Headers**

```
Authorization: Bearer {token}
Accept: application/json
```

#### **Respuesta Exitosa (200)**

```json
{
    "success": true,
    "message": "Sesión cerrada exitosamente"
}
```

---

## 🚪 **5. Cerrar Todas las Sesiones**

### **POST** `/logout-all`

Cierra todas las sesiones del usuario (revoca todos los tokens).

#### **Headers**

```
Authorization: Bearer {token}
Accept: application/json
```

#### **Respuesta Exitosa (200)**

```json
{
    "success": true,
    "message": "Todas las sesiones han sido cerradas exitosamente"
}
```

---

## 🔄 **6. Refrescar Token**

### **POST** `/refresh`

Crea un nuevo token y revoca el actual.

#### **Headers**

```
Authorization: Bearer {token}
Accept: application/json
```

#### **Respuesta Exitosa (200)**

```json
{
    "success": true,
    "message": "Token refrescado exitosamente",
    "data": {
        "token": "2|xyz789abc123...",
        "token_type": "Bearer"
    }
}
```

---

## 🛡️ **7. Ruta Protegida de Ejemplo**

### **GET** `/user`

Ejemplo de ruta que requiere autenticación.

#### **Headers**

```
Authorization: Bearer {token}
Accept: application/json
```

#### **Respuesta Exitosa (200)**

```json
{
    "success": true,
    "message": "Usuario autenticado",
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@ejemplo.com",
            "email_verified_at": null,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "updated_at": "2024-01-15T10:30:00.000000Z"
        }
    }
}
```

---

## 📋 **Códigos de Estado HTTP**

| Código | Descripción                                    |
| ------ | ---------------------------------------------- |
| 200    | OK - Operación exitosa                         |
| 201    | Created - Recurso creado exitosamente          |
| 401    | Unauthorized - No autenticado o token inválido |
| 422    | Unprocessable Entity - Error de validación     |
| 500    | Internal Server Error - Error del servidor     |

---

## 🔧 **Ejemplos de Uso con cURL**

### Registro

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "juan@ejemplo.com",
    "password": "password123"
  }'
```

### Obtener información del usuario

```bash
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

### Logout

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

---

## 🚀 **Configuración del Cliente**

### Headers Requeridos

Para todas las peticiones autenticadas, incluir:

```
Authorization: Bearer {token}
Accept: application/json
```

### Manejo de Errores

Siempre verificar el campo `success` en la respuesta:

-   `true`: Operación exitosa
-   `false`: Error en la operación

### Almacenamiento del Token

Guardar el token recibido en el login/register para usarlo en peticiones posteriores.

---

## 🔒 **Seguridad**

-   Los tokens tienen expiración configurable
-   Se recomienda usar HTTPS en producción
-   Los tokens se revocan automáticamente al hacer logout
-   Validación estricta de datos de entrada
-   Manejo seguro de contraseñas con hash bcrypt
