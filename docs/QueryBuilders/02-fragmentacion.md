# 2. Fragmentación

## Introducción

La fragmentación (chunking) es una técnica esencial para procesar grandes conjuntos de datos sin consumir demasiada memoria. Laravel proporciona varios métodos para dividir los resultados en fragmentos manejables.

## Métodos de Fragmentación

### chunk() - Fragmentación Básica

El método `chunk()` procesa los resultados en fragmentos de un tamaño específico.

```php
// Query Builder
DB::table('users')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        // Procesar cada usuario
        echo "Procesando usuario: {$user->name}\n";
    }
});

// Eloquent
User::orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        // Procesar cada usuario
        echo "Procesando usuario: {$user->name}\n";
    }
});
```

### chunkById() - Fragmentación por ID

Más eficiente que `chunk()` porque usa el ID para la paginación, evitando problemas con registros que cambian durante el procesamiento.

```php
// Query Builder
DB::table('users')->chunkById(100, function ($users) {
    foreach ($users as $user) {
        // Procesar cada usuario
        echo "Procesando usuario ID: {$user->id}\n";
    }
});

// Eloquent
User::chunkById(100, function ($users) {
    foreach ($users as $user) {
        // Procesar cada usuario
        echo "Procesando usuario ID: {$user->id}\n";
    }
});
```

### chunkById() con Columna Personalizada

```php
// Usar una columna diferente al ID
User::chunkById(100, function ($users) {
    foreach ($users as $user) {
        // Procesar cada usuario
    }
}, 'email'); // Usar email como columna de paginación
```

## Cursor Pagination

### cursor() - Iteración Lazy

El método `cursor()` devuelve un generador que itera sobre los resultados uno por uno, manteniendo solo un registro en memoria a la vez.

```php
// Query Builder
$users = DB::table('users')->cursor();

foreach ($users as $user) {
    // Procesar cada usuario
    echo "Procesando: {$user->name}\n";
}

// Eloquent
$users = User::cursor();

foreach ($users as $user) {
    // Procesar cada usuario
    echo "Procesando: {$user->name}\n";
}
```

### cursor() con Condiciones

```php
// Query Builder
$activeUsers = DB::table('users')
    ->where('status', 'active')
    ->orderBy('id')
    ->cursor();

foreach ($activeUsers as $user) {
    // Procesar solo usuarios activos
    echo "Usuario activo: {$user->name}\n";
}

// Eloquent
$activeUsers = User::where('status', 'active')
    ->orderBy('id')
    ->cursor();

foreach ($activeUsers as $user) {
    // Procesar solo usuarios activos
    echo "Usuario activo: {$user->name}\n";
}
```

## Lazy Collections

### lazy() - Colección Perezosa

El método `lazy()` devuelve una LazyCollection que carga los resultados en fragmentos automáticamente.

```php
// Query Builder
$users = DB::table('users')->lazy();

foreach ($users as $user) {
    // Procesar cada usuario
    echo "Procesando: {$user->name}\n";
}

// Eloquent
$users = User::lazy();

foreach ($users as $user) {
    // Procesar cada usuario
    echo "Procesando: {$user->name}\n";
}
```

### lazy() con Tamaño de Fragmento Personalizado

```php
// Query Builder
$users = DB::table('users')->lazy(50); // Fragmentos de 50

// Eloquent
$users = User::lazy(50); // Fragmentos de 50
```

## Ejemplos Prácticos

### Procesamiento de Usuarios Masivo

```php
public function processAllUsers()
{
    $processed = 0;
    $errors = 0;

    User::chunkById(100, function ($users) use (&$processed, &$errors) {
        foreach ($users as $user) {
            try {
                // Procesar usuario
                $this->processUser($user);
                $processed++;
            } catch (Exception $e) {
                $errors++;
                Log::error("Error procesando usuario {$user->id}: " . $e->getMessage());
            }
        }
    });

    return [
        'processed' => $processed,
        'errors' => $errors
    ];
}

private function processUser($user)
{
    // Lógica de procesamiento
    $user->update([
        'last_processed_at' => now(),
        'processed_count' => $user->processed_count + 1
    ]);
}
```

### Migración de Datos

```php
public function migrateUserData()
{
    $migrated = 0;

    User::whereNull('email_verified_at')
        ->chunkById(50, function ($users) use (&$migrated) {
            foreach ($users as $user) {
                // Migrar datos del usuario
                $user->update([
                    'email_verified_at' => $user->created_at,
                    'email_verification_token' => null
                ]);
                $migrated++;
            }
        });

    return "Migrados {$migrated} usuarios";
}
```

### Generación de Reportes

```php
public function generateUserReport()
{
    $report = [];
    $totalUsers = 0;
    $activeUsers = 0;
    $inactiveUsers = 0;

    User::chunkById(200, function ($users) use (&$report, &$totalUsers, &$activeUsers, &$inactiveUsers) {
        foreach ($users as $user) {
            $totalUsers++;

            if ($user->status === 'active') {
                $activeUsers++;
            } else {
                $inactiveUsers++;
            }

            // Agrupar por mes de creación
            $month = $user->created_at->format('Y-m');
            if (!isset($report[$month])) {
                $report[$month] = 0;
            }
            $report[$month]++;
        }
    });

    return [
        'total_users' => $totalUsers,
        'active_users' => $activeUsers,
        'inactive_users' => $inactiveUsers,
        'monthly_registrations' => $report
    ];
}
```

### Limpieza de Datos

```php
public function cleanupOldData()
{
    $deleted = 0;
    $cutoffDate = now()->subYears(2);

    User::where('created_at', '<', $cutoffDate)
        ->where('status', 'inactive')
        ->chunkById(100, function ($users) use (&$deleted) {
            foreach ($users as $user) {
                // Eliminar usuario y datos relacionados
                $user->posts()->delete();
                $user->comments()->delete();
                $user->delete();
                $deleted++;
            }
        });

    return "Eliminados {$deleted} usuarios antiguos";
}
```

## Optimización de Memoria

### Comparación de Métodos

```php
// ❌ Consume mucha memoria
$users = User::all(); // Carga todos los usuarios en memoria
foreach ($users as $user) {
    // Procesar
}

// ✅ Eficiente en memoria
User::chunkById(100, function ($users) {
    foreach ($users as $user) {
        // Procesar
    }
});

// ✅ Muy eficiente en memoria
$users = User::cursor();
foreach ($users as $user) {
    // Procesar
}
```

### Monitoreo de Memoria

```php
public function processWithMemoryMonitoring()
{
    $initialMemory = memory_get_usage();

    User::chunkById(100, function ($users) use ($initialMemory) {
        foreach ($users as $user) {
            // Procesar usuario
            $this->processUser($user);
        }

        // Verificar uso de memoria
        $currentMemory = memory_get_usage();
        $memoryUsed = $currentMemory - $initialMemory;

        Log::info("Memoria utilizada: " . number_format($memoryUsed / 1024 / 1024, 2) . " MB");
    });
}
```

## Casos de Uso Avanzados

### Procesamiento con Relaciones

```php
public function processUsersWithPosts()
{
    User::with('posts')->chunkById(50, function ($users) {
        foreach ($users as $user) {
            // Procesar usuario y sus posts
            $postCount = $user->posts->count();
            $user->update(['total_posts' => $postCount]);

            foreach ($user->posts as $post) {
                // Procesar cada post
                $this->processPost($post);
            }
        }
    });
}
```

### Procesamiento Condicional

```php
public function processUsersConditionally()
{
    User::chunkById(100, function ($users) {
        foreach ($users as $user) {
            // Procesar solo si cumple condiciones
            if ($this->shouldProcessUser($user)) {
                $this->processUser($user);
            }
        }
    });
}

private function shouldProcessUser($user)
{
    return $user->status === 'active' &&
           $user->created_at->diffInDays(now()) > 30;
}
```

### Procesamiento con Transacciones

```php
public function processUsersWithTransactions()
{
    User::chunkById(50, function ($users) {
        DB::transaction(function () use ($users) {
            foreach ($users as $user) {
                // Procesar usuario dentro de transacción
                $this->processUser($user);
            }
        });
    });
}
```

## Mejores Prácticas

### 1. Usar chunkById() en Lugar de chunk()

```php
// ✅ Recomendado - más eficiente
User::chunkById(100, function ($users) {
    // Procesar
});

// ❌ Menos eficiente
User::chunk(100, function ($users) {
    // Procesar
});
```

### 2. Ordenar por ID para chunkById()

```php
// ✅ Recomendado
User::orderBy('id')->chunkById(100, function ($users) {
    // Procesar
});

// ❌ Puede causar problemas
User::orderBy('name')->chunkById(100, function ($users) {
    // Procesar
});
```

### 3. Usar cursor() para Procesamiento Simple

```php
// ✅ Para procesamiento simple
$users = User::cursor();
foreach ($users as $user) {
    // Procesar
}

// ✅ Para procesamiento complejo
User::chunkById(100, function ($users) {
    foreach ($users as $user) {
        // Procesar con lógica compleja
    }
});
```

### 4. Manejar Errores Apropiadamente

```php
User::chunkById(100, function ($users) {
    foreach ($users as $user) {
        try {
            $this->processUser($user);
        } catch (Exception $e) {
            Log::error("Error procesando usuario {$user->id}: " . $e->getMessage());
            // Continuar con el siguiente usuario
        }
    }
});
```

### 5. Monitorear el Progreso

```php
public function processWithProgress()
{
    $total = User::count();
    $processed = 0;

    User::chunkById(100, function ($users) use (&$processed, $total) {
        foreach ($users as $user) {
            $this->processUser($user);
            $processed++;

            // Mostrar progreso cada 1000 usuarios
            if ($processed % 1000 === 0) {
                $percentage = round(($processed / $total) * 100, 2);
                echo "Progreso: {$processed}/{$total} ({$percentage}%)\n";
            }
        }
    });
}
```

## Debugging y Monitoreo

### Verificar Fragmentación

```php
// Verificar que la fragmentación funciona correctamente
$chunkCount = 0;
User::chunkById(100, function ($users) use (&$chunkCount) {
    $chunkCount++;
    echo "Fragmento {$chunkCount}: " . $users->count() . " usuarios\n";
});

echo "Total de fragmentos: {$chunkCount}\n";
```

### Medir Rendimiento

```php
public function measureChunkPerformance()
{
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    $processed = 0;
    User::chunkById(100, function ($users) use (&$processed) {
        foreach ($users as $user) {
            $this->processUser($user);
            $processed++;
        }
    });

    $endTime = microtime(true);
    $endMemory = memory_get_usage();

    $executionTime = $endTime - $startTime;
    $memoryUsed = $endMemory - $startMemory;

    return [
        'processed' => $processed,
        'execution_time' => round($executionTime, 2) . ' segundos',
        'memory_used' => round($memoryUsed / 1024 / 1024, 2) . ' MB'
    ];
}
```

---

**Próximo**: [Transmisión de Resultados Perezosamente](./03-transmision-perezosa.md)
