# Programación Orientada a Objetos (POO) en Laravel

## Índice de Contenidos

Esta guía cubre todos los conceptos de Programación Orientada a Objetos aplicados específicamente a Laravel:

### 1. [Fundamentos de POO](01-fundamentos-poo.md)

-   Clases y Objetos
-   Propiedades y Métodos
-   Encapsulamiento
-   Herencia
-   Polimorfismo
-   Abstracción

### 2. [Clases en Laravel](02-clases-laravel.md)

-   Estructura de una Clase
-   Namespaces
-   Autoloading
-   Clases de Servicio
-   Clases Utilitarias

### 3. [Modelos Eloquent](03-modelos-eloquent.md)

-   Definición de Modelos
-   Propiedades y Atributos
-   Métodos de Instancia
-   Métodos Estáticos
-   Relaciones entre Modelos

### 4. [Controladores](04-controladores.md)

-   Estructura de Controladores
-   Métodos de Acción
-   Inyección de Dependencias
-   Resource Controllers
-   API Controllers

### 5. [Inyección de Dependencias](05-inyeccion-dependencias.md)

-   Container de Laravel
-   Binding de Interfaces
-   Resolución Automática
-   Singleton y Transient
-   Contextual Binding

### 6. [Interfaces y Contratos](06-interfaces-contratos.md)

-   Definición de Interfaces
-   Implementación
-   Contratos de Laravel
-   Interfaces Personalizadas
-   Testing con Interfaces

### 7. [Traits](07-traits.md)

-   Definición de Traits
-   Uso de Traits
-   Conflictos de Métodos
-   Traits en Laravel
-   Traits Comunes

### 8. [Herencia y Composición](08-herencia-composicion.md)

-   Herencia Simple
-   Herencia Múltiple con Traits
-   Composición vs Herencia
-   Patrones de Diseño
-   Mejores Prácticas

### 9. [Polimorfismo](09-polimorfismo.md)

-   Polimorfismo de Métodos
-   Polimorfismo de Interfaces
-   Polimorfismo en Eloquent
-   Polimorfismo en Controllers
-   Ejemplos Prácticos

### 10. [Encapsulamiento](10-encapsulamiento.md)

-   Modificadores de Acceso
-   Getters y Setters
-   Propiedades Computadas
-   Métodos Privados
-   Protección de Datos

### 11. [Abstracción](11-abstraccion.md)

-   Clases Abstractas
-   Métodos Abstractos
-   Abstracción en Laravel
-   Interfaces Abstractas
-   Patrones de Abstracción

### 12. [Patrones de Diseño](12-patrones-diseno.md)

-   Singleton
-   Factory
-   Repository
-   Service Layer
-   Observer
-   Strategy

### 13. [Clases de Servicio](13-clases-servicio.md)

-   Definición de Servicios
-   Lógica de Negocio
-   Reutilización de Código
-   Testing de Servicios
-   Mejores Prácticas

### 14. [Validación y Form Requests](14-validacion-form-requests.md)

-   Form Request Classes
-   Reglas de Validación
-   Mensajes Personalizados
-   Validación Condicional
-   Autorización

### 15. [Middleware](15-middleware.md)

-   Definición de Middleware
-   Middleware Global
-   Middleware de Ruta
-   Middleware de Grupo
-   Middleware Personalizado

### 16. [Jobs y Queues](16-jobs-queues.md)

-   Job Classes
-   Queue Jobs
-   Failed Jobs
-   Job Batching
-   Job Chaining

### 17. [Events y Listeners](17-events-listeners.md)

-   Event Classes
-   Listener Classes
-   Event Broadcasting
-   Event Subscribers
-   Event Testing

### 18. [Notifications](18-notifications.md)

-   Notification Classes
-   Canales de Notificación
-   Notificaciones de Base de Datos
-   Notificaciones por Email
-   Notificaciones Personalizadas

### 19. [Policies y Gates](19-policies-gates.md)

-   Policy Classes
-   Gates
-   Autorización en Controllers
-   Autorización en Blade
-   Autorización en API

### 20. [Macros](20-macros.md)

-   Macros en Eloquent
-   Macros en Collections
-   Macros en Request
-   Macros en Response
-   Macros Personalizadas

### 21. [Testing](21-testing.md)

-   Unit Testing
-   Feature Testing
-   Model Testing
-   Controller Testing
-   Service Testing

## Modelos de Ejemplo

Para los ejemplos en esta documentación, utilizaremos estos modelos:

### User Model

```php
class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Post Model

```php
class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Comment Model

```php
class Comment extends Model
{
    protected $fillable = ['content', 'user_id', 'post_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

## Estructura de Base de Datos

```sql
-- Tabla users
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabla posts
CREATE TABLE posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabla comments
CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);
```

## Objetivos de Aprendizaje

Al completar esta guía, serás capaz de:

1. **Comprender los fundamentos de POO** y cómo se aplican en Laravel
2. **Crear y estructurar clases** siguiendo las mejores prácticas de Laravel
3. **Implementar patrones de diseño** comunes en aplicaciones Laravel
4. **Utilizar características avanzadas** como Traits, Interfaces y Clases Abstractas
5. **Desarrollar código mantenible** y escalable siguiendo principios SOLID
6. **Implementar testing** efectivo para clases y métodos
7. **Aplicar conceptos de POO** en situaciones reales de desarrollo

## Prerrequisitos

-   Conocimiento básico de PHP
-   Familiaridad con Laravel (rutas, controladores básicos, modelos)
-   Comprensión de conceptos básicos de bases de datos
-   Experiencia con Git y control de versiones

## Recursos Adicionales

-   [Documentación oficial de Laravel](https://laravel.com/docs)
-   [PHP Documentation](https://www.php.net/manual/en/)
-   [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
-   [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
