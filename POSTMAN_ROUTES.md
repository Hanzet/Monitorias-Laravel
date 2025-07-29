# 🚀 Rutas para Postman - API de Autenticación

## 📋 Configuración Inicial

### Base URL

```
http://localhost:8000/api
```

### Variables de Entorno

| Variable     | Valor                      |
| ------------ | -------------------------- |
| `base_url`   | `http://localhost:8000`    |
| `auth_token` | (se llena automáticamente) |

---

## 🔐 **ENDPOINTS DE AUTENTICACIÓN**

### **1. REGISTRO DE USUARIO**

```
Método: POST
URL: {{base_url}}/api/register

Headers:
Content-Type: application/json
Accept: application/json

Body (raw JSON):
{
    "name": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### **2. INICIO DE SESIÓN**

```
Método: POST
URL: {{base_url}}/api/login

Headers:
Content-Type: application/json
Accept: application/json

Body (raw JSON):
{
    "email": "juan@ejemplo.com",
    "password": "password123"
}
```

### **3. OBTENER PERFIL DEL USUARIO**

```
Método: GET
URL: {{base_url}}/api/me

Headers:
Authorization: Bearer {{auth_token}}
Accept: application/json
```

### **4. CERRAR SESIÓN**

```
Método: POST
URL: {{base_url}}/api/logout

Headers:
Authorization: Bearer {{auth_token}}
Accept: application/json
```

### **5. CERRAR TODAS LAS SESIONES**

```
Método: POST
URL: {{base_url}}/api/logout-all

Headers:
Authorization: Bearer {{auth_token}}
Accept: application/json
```

### **6. REFRESCAR TOKEN**

```
Método: POST
URL: {{base_url}}/api/refresh

Headers:
Authorization: Bearer {{auth_token}}
Accept: application/json
```

### **7. RUTA PROTEGIDA DE EJEMPLO**

```
Método: GET
URL: {{base_url}}/api/user

Headers:
Authorization: Bearer {{auth_token}}
Accept: application/json
```

---

## 🔧 **CONFIGURACIÓN DE POSTMAN**

### **1. Crear Environment**

1. Click en el ícono de engranaje (⚙️)
2. Click en "Add"
3. Nombre: "Laravel Auth API"
4. Agregar variables:
    - `base_url`: `http://localhost:8000`
    - `auth_token`: (dejar vacío)

### **2. Script para Auto-guardar Token**

En las peticiones de **Login** y **Register**, en la pestaña **Tests**:

```javascript
if (pm.response.code === 200 || pm.response.code === 201) {
    const response = pm.response.json();
    if (response.data && response.data.token) {
        pm.environment.set("auth_token", response.data.token);
        console.log("Token guardado:", response.data.token);
    }
}
```

### **3. Importar Colección**

1. Click en "Import"
2. Seleccionar el archivo `postman_collection.json`
3. Seleccionar el environment creado

---

## 📝 **EJEMPLOS DE USUARIO PARA PRUEBAS**

### **Usuario 1:**

```json
{
    "name": "Juan Pérez",
    "email": "juan@ejemplo.com",
    "password": "password123"
}
```

### **Usuario 2:**

```json
{
    "name": "María García",
    "email": "maria@ejemplo.com",
    "password": "password123"
}
```

### **Usuario 3:**

```json
{
    "name": "Carlos López",
    "email": "carlos@ejemplo.com",
    "password": "password123"
}
```

---

## 🔄 **FLUJO DE PRUEBAS RECOMENDADO**

### **Paso 1: Registro**

1. Usar endpoint `/register`
2. Verificar que se guarde el token automáticamente

### **Paso 2: Obtener Perfil**

1. Usar endpoint `/me`
2. Verificar que devuelva la información del usuario

### **Paso 3: Refrescar Token**

1. Usar endpoint `/refresh`
2. Verificar que se genere un nuevo token

### **Paso 4: Login con Otro Usuario**

1. Usar endpoint `/login`
2. Verificar que se actualice el token

### **Paso 5: Logout**

1. Usar endpoint `/logout`
2. Verificar que el token se revoque

### **Paso 6: Intentar Acceso sin Token**

1. Usar endpoint `/me` sin Authorization header
2. Verificar que devuelva 401

---

## 🚨 **CÓDIGOS DE RESPUESTA ESPERADOS**

| Endpoint      | Método | Código Exitoso | Código Error |
| ------------- | ------ | -------------- | ------------ |
| `/register`   | POST   | 201            | 422          |
| `/login`      | POST   | 200            | 401          |
| `/me`         | GET    | 200            | 401          |
| `/logout`     | POST   | 200            | 401          |
| `/logout-all` | POST   | 200            | 401          |
| `/refresh`    | POST   | 200            | 401          |
| `/user`       | GET    | 200            | 401          |

---

## 📊 **ESTRUCTURA DE RESPUESTA**

### **Respuesta Exitosa:**

```json
{
    "success": true,
    "message": "Mensaje de éxito",
    "data": {
        // Datos específicos del endpoint
    }
}
```

### **Respuesta de Error:**

```json
{
    "success": false,
    "message": "Mensaje de error",
    "errors": {
        // Errores de validación (si aplica)
    }
}
```

---

## 🎯 **TIPS PARA POSTMAN**

1. **Usar Variables:** Siempre usar `{{base_url}}` en lugar de la URL completa
2. **Auto-guardar Token:** El script automático guarda el token después del login/register
3. **Headers Consistentes:** Siempre incluir `Accept: application/json`
4. **Body JSON:** Usar formato raw JSON para los bodies
5. **Tests:** Agregar assertions para verificar respuestas
6. **Environment:** Cambiar entre environments según el entorno (local, staging, production)

---

## 🔍 **VERIFICACIONES IMPORTANTES**

### **Después del Registro:**

-   ✅ Status 201
-   ✅ Token en la respuesta
-   ✅ Usuario creado en la base de datos

### **Después del Login:**

-   ✅ Status 200
-   ✅ Token en la respuesta
-   ✅ Información del usuario correcta

### **Después del Logout:**

-   ✅ Status 200
-   ✅ Token revocado (no funciona en `/me`)

### **Acceso sin Token:**

-   ✅ Status 401
-   ✅ Mensaje de error apropiado
