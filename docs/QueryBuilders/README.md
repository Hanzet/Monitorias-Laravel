# Guía Completa de Query Builders y Eloquent en Laravel

## Índice de Contenidos

Esta guía cubre todos los aspectos fundamentales de Query Builders y Eloquent ORM en Laravel, con ejemplos prácticos y casos de uso comunes.

### 1. [Ejecución de Consultas de Base de Datos](./01-ejecucion-consultas.md)

-   Consultas básicas con Query Builder
-   Consultas con Eloquent ORM
-   Diferentes métodos de ejecución

### 2. [Fragmentación](./02-fragmentacion.md)

-   Procesamiento de grandes conjuntos de datos
-   Chunking y cursor pagination
-   Optimización de memoria

### 3. [Transmisión de Resultados Perezosamente](./03-transmision-perezosa.md)

-   Lazy Collections
-   Generadores y streams
-   Eficiencia en el procesamiento

### 4. [Agregados](./04-agregados.md)

-   Funciones de agregación (COUNT, SUM, AVG, etc.)
-   Agregados con condiciones
-   Agregados por grupos

### 5. [Cláusula de Selección](./05-clausula-seleccion.md)

-   Selección de columnas específicas
-   Aliases y expresiones
-   Subconsultas en SELECT

### 6. [Expresiones Sin Procesar](./06-expresiones-sin-procesar.md)

-   Raw SQL en consultas
-   Expresiones de base de datos
-   Funciones nativas de SQL

### 7. [Cláusula Inner Join](./07-clausula-inner-join.md)

-   Joins básicos
-   Múltiples joins
-   Joins con condiciones

### 8. [Cláusula Where](./08-clausula-where.md)

-   Condiciones básicas
-   Operadores de comparación
-   Condiciones múltiples

### 9. [Cláusula OrWhere](./09-clausula-orwhere.md)

-   Condiciones OR
-   Agrupación de condiciones
-   Combinación con WHERE

### 10. [Cláusula WhereNot](./10-clausula-wherenot.md)

-   Condiciones de negación
-   NOT IN, NOT EXISTS
-   Excepciones en consultas

### 11. [Cláusulas Where Adicionales](./11-clausulas-where-adicionales.md)

-   WhereBetween, WhereIn, WhereNull
-   WhereDate, WhereTime, WhereYear
-   Condiciones avanzadas

### 12. [Agrupación Lógica](./12-agrupacion-logica.md)

-   Paréntesis en consultas
-   Agrupación de condiciones
-   Precedencia de operadores

### 13. [Ordenar Registros](./13-ordenar-registros.md)

-   ORDER BY básico
-   Ordenamiento múltiple
-   Ordenamiento aleatorio

### 14. [Agrupar Registros](./14-agrupar-registros.md)

-   GROUP BY básico
-   Agrupación con agregados
-   HAVING clause

### 15. [Límite y Compensación de Registros](./15-limite-compensacion.md)

-   LIMIT y OFFSET
-   Paginación manual
-   Optimización de consultas

### 16. [Cláusulas Condicionales](./16-clausulas-condicionales.md)

-   Consultas condicionales
-   When/Unless methods
-   Lógica dinámica

### 17. [Insertar Registros](./17-insertar-registros.md)

-   Insert básico
-   Insert múltiple
-   Insert con Eloquent

### 18. [Actualizar Registros](./18-actualizar-registros.md)

-   Update básico
-   Update con condiciones
-   Update con Eloquent

### 19. [Incrementar y Decrementar](./19-incrementar-decrementar.md)

-   Increment/Decrement
-   Operaciones atómicas
-   Campos numéricos

### 20. [Eliminar Registros](./20-eliminar-registros.md)

-   Delete básico
-   Delete con condiciones
-   Soft deletes

### 21. [Paginación](./21-paginacion.md)

-   Paginación automática
-   Paginación manual
-   Paginación con Eloquent

## Modelos de Ejemplo

Para todos los ejemplos de esta guía, utilizaremos los siguientes modelos:

### User Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'created_at'
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Post Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'status',
        'published_at',
        'views_count'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'views_count' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

### Comment Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'content',
        'user_id',
        'post_id',
        'rating'
    ];

    protected $casts = [
        'rating' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
```

## Estructura de Base de Datos

### Tabla users

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Tabla posts

```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    published_at TIMESTAMP NULL,
    views_count INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Tabla comments

```sql
CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);
```

## Convenciones de la Guía

-   Todos los ejemplos incluyen tanto Query Builder como Eloquent
-   Se muestran casos de uso reales y prácticos
-   Incluye optimizaciones y mejores prácticas
-   Ejemplos de código listos para usar
-   Explicaciones detalladas de cada concepto

## Próximos Pasos

1. Revisa cada sección en orden para un aprendizaje progresivo
2. Practica con los ejemplos en tu proyecto Laravel
3. Adapta los ejemplos a tus necesidades específicas
4. Consulta la documentación oficial de Laravel para más detalles

---

**Nota**: Esta guía está diseñada para desarrolladores con conocimientos básicos de Laravel y SQL. Si necesitas repasar conceptos fundamentales, consulta la documentación oficial de Laravel.
