# 3. Transmisión de Resultados Perezosamente

## Introducción

La transmisión perezosa (lazy loading) permite procesar grandes conjuntos de datos de manera eficiente, cargando solo los datos necesarios cuando se requieren. Laravel proporciona varios métodos para implementar esta técnica.

## Lazy Collections

### lazy() - Colección Perezosa Básica

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

### lazy() con Condiciones

```php
// Query Builder
$activeUsers = DB::table('users')
    ->where('status', 'active')
    ->orderBy('id')
    ->lazy();

// Eloquent
$activeUsers = User::where('status', 'active')
    ->orderBy('id')
    ->lazy();
```

## Cursor Pagination

### cursor() - Iteración Lazy

El método `cursor()` devuelve un generador que itera sobre los resultados uno por uno.

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

## Generadores Personalizados

### Crear Generadores para Procesamiento Complejo

```php
public function generateUserProcessor()
{
    return function () {
        $users = User::cursor();

        foreach ($users as $user) {
            // Procesar usuario
            $processedUser = $this->processUser($user);

            // Yield el resultado procesado
            yield $processedUser;
        }
    };
}

public function processUsersLazily()
{
    $processor = $this->generateUserProcessor();

    foreach ($processor() as $processedUser) {
        // Hacer algo con el usuario procesado
        echo "Usuario procesado: {$processedUser['name']}\n";
    }
}
```

### Generador con Filtros

```php
public function generateFilteredUsers($status = null)
{
    return function () use ($status) {
        $query = User::query();

        if ($status) {
            $query->where('status', $status);
        }

        $users = $query->cursor();

        foreach ($users as $user) {
            // Aplicar filtros adicionales
            if ($this->shouldProcessUser($user)) {
                yield $user;
            }
        }
    };
}

public function processFilteredUsers()
{
    $activeUsers = $this->generateFilteredUsers('active');

    foreach ($activeUsers() as $user) {
        $this->processUser($user);
    }
}
```

## Lazy Collections Avanzadas

### Métodos de LazyCollection

```php
// Filtrar perezosamente
$users = User::lazy()
    ->filter(function ($user) {
        return $user->status === 'active';
    })
    ->map(function ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ];
    });

foreach ($users as $user) {
    // Procesar usuario filtrado y mapeado
    echo "Usuario: {$user['name']}\n";
}
```

### LazyCollection con Transformaciones

```php
// Transformar datos perezosamente
$userStats = User::lazy()
    ->groupBy(function ($user) {
        return $user->created_at->format('Y-m');
    })
    ->map(function ($group) {
        return $group->count();
    });

foreach ($userStats as $month => $count) {
    echo "Mes: {$month}, Usuarios: {$count}\n";
}
```

## Ejemplos Prácticos

### Procesamiento de Archivos Grandes

```php
public function processLargeFile($filePath)
{
    return function () use ($filePath) {
        $handle = fopen($filePath, 'r');

        while (($line = fgets($handle)) !== false) {
            $data = json_decode($line, true);

            if ($data) {
                yield $data;
            }
        }

        fclose($handle);
    };
}

public function importUsersFromFile($filePath)
{
    $processor = $this->processLargeFile($filePath);
    $imported = 0;

    foreach ($processor() as $userData) {
        try {
            User::create($userData);
            $imported++;
        } catch (Exception $e) {
            Log::error("Error importando usuario: " . $e->getMessage());
        }
    }

    return "Importados {$imported} usuarios";
}
```

### Streaming de Datos

```php
public function streamUserData()
{
    $users = User::lazy();

    foreach ($users as $user) {
        // Enviar datos al cliente
        $this->sendUserData($user);

        // Pausa para no sobrecargar
        usleep(1000); // 1ms
    }
}

private function sendUserData($user)
{
    // Lógica para enviar datos al cliente
    echo json_encode($user) . "\n";
}
```

### Procesamiento con Memoria Limitada

```php
public function processWithMemoryLimit($memoryLimit = '128M')
{
    $users = User::lazy();
    $processed = 0;

    foreach ($users as $user) {
        // Verificar uso de memoria
        if (memory_get_usage() > $this->parseMemoryLimit($memoryLimit)) {
            // Liberar memoria
            gc_collect_cycles();
        }

        $this->processUser($user);
        $processed++;
    }

    return "Procesados {$processed} usuarios";
}

private function parseMemoryLimit($limit)
{
    $unit = strtolower(substr($limit, -1));
    $value = (int) substr($limit, 0, -1);

    switch ($unit) {
        case 'k': return $value * 1024;
        case 'm': return $value * 1024 * 1024;
        case 'g': return $value * 1024 * 1024 * 1024;
        default: return $value;
    }
}
```

## Optimización de Rendimiento

### Comparación de Métodos

```php
// ❌ Consume mucha memoria
$users = User::all();
foreach ($users as $user) {
    // Procesar
}

// ✅ Eficiente en memoria
$users = User::lazy();
foreach ($users as $user) {
    // Procesar
}

// ✅ Muy eficiente en memoria
$users = User::cursor();
foreach ($users as $user) {
    // Procesar
}
```

### Monitoreo de Memoria

```php
public function monitorMemoryUsage()
{
    $initialMemory = memory_get_usage();
    $peakMemory = $initialMemory;

    $users = User::lazy();

    foreach ($users as $user) {
        $this->processUser($user);

        $currentMemory = memory_get_usage();
        $peakMemory = max($peakMemory, $currentMemory);

        // Log cada 1000 usuarios
        if ($user->id % 1000 === 0) {
            $memoryUsed = $currentMemory - $initialMemory;
            Log::info("Usuario {$user->id}: Memoria usada: " .
                     number_format($memoryUsed / 1024 / 1024, 2) . " MB");
        }
    }

    $totalMemoryUsed = $peakMemory - $initialMemory;
    Log::info("Memoria máxima usada: " .
             number_format($totalMemoryUsed / 1024 / 1024, 2) . " MB");
}
```

## Casos de Uso Avanzados

### Procesamiento con Relaciones Lazy

```php
public function processUsersWithLazyRelations()
{
    $users = User::lazy();

    foreach ($users as $user) {
        // Cargar relaciones solo cuando se necesiten
        $posts = $user->posts()->lazy();

        foreach ($posts as $post) {
            $this->processPost($post);
        }
    }
}
```

### Pipeline de Procesamiento

```php
public function createProcessingPipeline()
{
    return User::lazy()
        ->filter(function ($user) {
            return $user->status === 'active';
        })
        ->map(function ($user) {
            return $this->enrichUserData($user);
        })
        ->filter(function ($user) {
            return $this->shouldProcessUser($user);
        });
}

public function runPipeline()
{
    $pipeline = $this->createProcessingPipeline();

    foreach ($pipeline as $user) {
        $this->processUser($user);
    }
}
```

### Procesamiento Paralelo con Lazy Collections

```php
public function processUsersInParallel($chunkSize = 100)
{
    $users = User::lazy($chunkSize);
    $chunks = [];

    foreach ($users as $user) {
        $chunks[] = $user;

        if (count($chunks) >= $chunkSize) {
            // Procesar chunk en paralelo
            $this->processChunkInParallel($chunks);
            $chunks = [];
        }
    }

    // Procesar chunk final
    if (!empty($chunks)) {
        $this->processChunkInParallel($chunks);
    }
}

private function processChunkInParallel($chunks)
{
    // Implementar procesamiento paralelo
    foreach ($chunks as $user) {
        $this->processUser($user);
    }
}
```

## Mejores Prácticas

### 1. Usar lazy() para Procesamiento Simple

```php
// ✅ Para procesamiento simple
$users = User::lazy();
foreach ($users as $user) {
    // Procesar
}
```

### 2. Usar cursor() para Máxima Eficiencia

```php
// ✅ Para máxima eficiencia
$users = User::cursor();
foreach ($users as $user) {
    // Procesar
}
```

### 3. Evitar Operaciones Costosas en Lazy Collections

```php
// ❌ Evitar operaciones costosas
$users = User::lazy()->toArray(); // Carga todo en memoria

// ✅ Mantener lazy
$users = User::lazy();
foreach ($users as $user) {
    // Procesar
}
```

### 4. Manejar Errores Apropiadamente

```php
$users = User::lazy();

foreach ($users as $user) {
    try {
        $this->processUser($user);
    } catch (Exception $e) {
        Log::error("Error procesando usuario {$user->id}: " . $e->getMessage());
        // Continuar con el siguiente usuario
    }
}
```

### 5. Monitorear el Progreso

```php
public function processWithProgress()
{
    $total = User::count();
    $processed = 0;

    $users = User::lazy();

    foreach ($users as $user) {
        $this->processUser($user);
        $processed++;

        // Mostrar progreso cada 1000 usuarios
        if ($processed % 1000 === 0) {
            $percentage = round(($processed / $total) * 100, 2);
            echo "Progreso: {$processed}/{$total} ({$percentage}%)\n";
        }
    }
}
```

## Debugging y Monitoreo

### Verificar Lazy Loading

```php
// Verificar que lazy loading funciona correctamente
$initialMemory = memory_get_usage();

$users = User::lazy();
$count = 0;

foreach ($users as $user) {
    $count++;

    if ($count % 1000 === 0) {
        $currentMemory = memory_get_usage();
        $memoryUsed = $currentMemory - $initialMemory;
        echo "Usuarios procesados: {$count}, Memoria: " .
             number_format($memoryUsed / 1024 / 1024, 2) . " MB\n";
    }
}

echo "Total procesados: {$count}\n";
```

### Medir Rendimiento

```php
public function measureLazyPerformance()
{
    $startTime = microtime(true);
    $startMemory = memory_get_usage();

    $processed = 0;
    $users = User::lazy();

    foreach ($users as $user) {
        $this->processUser($user);
        $processed++;
    }

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

**Próximo**: [Agregados](./04-agregados.md)
