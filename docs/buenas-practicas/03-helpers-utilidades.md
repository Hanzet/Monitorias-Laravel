# Helpers y Utilidades en Laravel

## ¿Qué son los Helpers?

Los helpers son funciones o clases utilitarias que proporcionan funcionalidades comunes y reutilizables en toda la aplicación, evitando la duplicación de código y mejorando la mantenibilidad.

## ¿Cuándo Usar Helpers?

### Casos de Uso Apropiados

-   **Funcionalidades comunes**: Formateo de fechas, validaciones, cálculos
-   **Operaciones repetitivas**: Generación de slugs, limpieza de datos
-   **Utilidades de negocio**: Cálculos específicos del dominio
-   **Funciones de presentación**: Formateo de moneda, texto, etc.

### Casos de Uso Inapropiados

-   **Lógica de negocio compleja**: Mejor usar servicios
-   **Operaciones de base de datos**: Mejor usar modelos o repositorios
-   **Validaciones complejas**: Mejor usar Form Requests
-   **Autenticación**: Mejor usar middleware o policies

## Tipos de Helpers

### 1. Helpers de Funciones

```php
// app/Helpers/StringHelper.php
class StringHelper
{
    /**
     * Genera un slug a partir de un texto
     */
    public static function generateSlug($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    /**
     * Limpia y formatea un número de teléfono
     */
    public static function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            return '(' . substr($phone, 0, 3) . ') ' .
                   substr($phone, 3, 3) . '-' .
                   substr($phone, 6, 4);
        }

        return $phone;
    }

    /**
     * Trunca texto a una longitud específica
     */
    public static function truncate($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Convierte texto a formato título
     */
    public static function toTitleCase($text)
    {
        return ucwords(strtolower($text));
    }
}

// Uso
$slug = StringHelper::generateSlug('Mi Título de Post');
$phone = StringHelper::formatPhone('1234567890');
$truncated = StringHelper::truncate('Texto muy largo...', 50);
```

### 2. Helpers de Fechas

```php
// app/Helpers/DateHelper.php
class DateHelper
{
    /**
     * Formatea fecha para mostrar
     */
    public static function formatForDisplay($date, $format = 'd/m/Y H:i')
    {
        if (!$date) return null;

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $date->format($format);
    }

    /**
     * Calcula edad a partir de fecha de nacimiento
     */
    public static function calculateAge($birthDate)
    {
        if (!$birthDate) return null;

        $birthDate = $birthDate instanceof Carbon ? $birthDate : Carbon::parse($birthDate);
        return $birthDate->age;
    }

    /**
     * Obtiene tiempo transcurrido en formato legible
     */
    public static function timeAgo($date)
    {
        if (!$date) return null;

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $date->diffForHumans();
    }

    /**
     * Verifica si una fecha es hoy
     */
    public static function isToday($date)
    {
        if (!$date) return false;

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $date->isToday();
    }

    /**
     * Obtiene el primer día del mes
     */
    public static function firstDayOfMonth($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        return $date->startOfMonth();
    }

    /**
     * Obtiene el último día del mes
     */
    public static function lastDayOfMonth($date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();
        return $date->endOfMonth();
    }
}

// Uso
$formattedDate = DateHelper::formatForDisplay($user->created_at);
$age = DateHelper::calculateAge($user->birth_date);
$timeAgo = DateHelper::timeAgo($post->created_at);
```

### 3. Helpers de Validación

```php
// app/Helpers/ValidationHelper.php
class ValidationHelper
{
    /**
     * Valida formato de email
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida formato de URL
     */
    public static function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Valida formato de teléfono
     */
    public static function isValidPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }

    /**
     * Valida formato de documento de identidad
     */
    public static function isValidDocument($document)
    {
        $document = preg_replace('/[^0-9]/', '', $document);
        return strlen($document) >= 8 && strlen($document) <= 12;
    }

    /**
     * Valida contraseña fuerte
     */
    public static function isStrongPassword($password)
    {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }
}

// Uso
if (ValidationHelper::isValidEmail($email)) {
    // Procesar email
}

if (ValidationHelper::isStrongPassword($password)) {
    // Contraseña válida
}
```

### 4. Helpers de Formateo

```php
// app/Helpers/FormatHelper.php
class FormatHelper
{
    /**
     * Formatea moneda
     */
    public static function formatCurrency($amount, $currency = 'USD')
    {
        $formatters = [
            'USD' => '$',
            'EUR' => '€',
            'COP' => '$'
        ];

        $symbol = $formatters[$currency] ?? $currency;
        return $symbol . number_format($amount, 2);
    }

    /**
     * Formatea número con separadores
     */
    public static function formatNumber($number, $decimals = 0)
    {
        return number_format($number, $decimals, ',', '.');
    }

    /**
     * Formatea tamaño de archivo
     */
    public static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Formatea porcentaje
     */
    public static function formatPercentage($value, $total, $decimals = 1)
    {
        if ($total == 0) return '0%';

        $percentage = ($value / $total) * 100;
        return round($percentage, $decimals) . '%';
    }

    /**
     * Formatea texto en formato de tarjeta de crédito
     */
    public static function formatCreditCard($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        return chunk_split($number, 4, ' ');
    }
}

// Uso
$price = FormatHelper::formatCurrency(1234.56);
$number = FormatHelper::formatNumber(1234567);
$fileSize = FormatHelper::formatFileSize(1024000);
$percentage = FormatHelper::formatPercentage(25, 100);
```

### 5. Helpers de Generación

```php
// app/Helpers/GeneratorHelper.php
class GeneratorHelper
{
    /**
     * Genera código único
     */
    public static function generateUniqueCode($length = 8)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $code;
    }

    /**
     * Genera nombre de archivo único
     */
    public static function generateUniqueFilename($extension)
    {
        return uniqid() . '_' . time() . '.' . $extension;
    }

    /**
     * Genera token de acceso
     */
    public static function generateAccessToken($length = 32)
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Genera PIN numérico
     */
    public static function generatePin($length = 4)
    {
        $pin = '';
        for ($i = 0; $i < $length; $i++) {
            $pin .= rand(0, 9);
        }
        return $pin;
    }
}

// Uso
$code = GeneratorHelper::generateUniqueCode();
$filename = GeneratorHelper::generateUniqueFilename('jpg');
$token = GeneratorHelper::generateAccessToken();
$pin = GeneratorHelper::generatePin();
```

### 6. Helpers de Array y Colecciones

```php
// app/Helpers/ArrayHelper.php
class ArrayHelper
{
    /**
     * Agrupa array por clave
     */
    public static function groupBy($array, $key)
    {
        $result = [];

        foreach ($array as $item) {
            $groupKey = is_array($item) ? $item[$key] : $item->$key;
            $result[$groupKey][] = $item;
        }

        return $result;
    }

    /**
     * Obtiene valores únicos de array
     */
    public static function unique($array, $key = null)
    {
        if ($key) {
            $values = array_column($array, $key);
            return array_unique($values);
        }

        return array_unique($array);
    }

    /**
     * Filtra array por múltiples condiciones
     */
    public static function filterBy($array, $conditions)
    {
        return array_filter($array, function($item) use ($conditions) {
            foreach ($conditions as $key => $value) {
                $itemValue = is_array($item) ? $item[$key] : $item->$key;
                if ($itemValue != $value) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Ordena array por múltiples criterios
     */
    public static function sortBy($array, $criteria)
    {
        usort($array, function($a, $b) use ($criteria) {
            foreach ($criteria as $key => $direction) {
                $aValue = is_array($a) ? $a[$key] : $a->$key;
                $bValue = is_array($b) ? $b[$key] : $b->$key;

                if ($aValue != $bValue) {
                    return $direction === 'desc' ?
                           ($bValue <=> $aValue) :
                           ($aValue <=> $bValue);
                }
            }
            return 0;
        });

        return $array;
    }
}

// Uso
$grouped = ArrayHelper::groupBy($users, 'role');
$uniqueEmails = ArrayHelper::unique($users, 'email');
$filtered = ArrayHelper::filterBy($posts, ['status' => 'published']);
$sorted = ArrayHelper::sortBy($products, ['price' => 'asc', 'name' => 'asc']);
```

## Helpers Específicos de Laravel

### 1. Helpers de Respuesta API

```php
// app/Helpers/ApiHelper.php
class ApiHelper
{
    /**
     * Respuesta exitosa
     */
    public static function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Respuesta de error
     */
    public static function error($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Respuesta de validación
     */
    public static function validationError($errors)
    {
        return self::error('Validation failed', 422, $errors);
    }

    /**
     * Respuesta de recurso no encontrado
     */
    public static function notFound($message = 'Resource not found')
    {
        return self::error($message, 404);
    }

    /**
     * Respuesta de acceso denegado
     */
    public static function forbidden($message = 'Access denied')
    {
        return self::error($message, 403);
    }
}

// Uso en controladores
class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);
        return ApiHelper::success($users, 'Users retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users'
        ]);

        if ($validator->fails()) {
            return ApiHelper::validationError($validator->errors());
        }

        $user = User::create($request->validated());
        return ApiHelper::success($user, 'User created successfully', 201);
    }
}
```

### 2. Helpers de Cache

```php
// app/Helpers/CacheHelper.php
class CacheHelper
{
    /**
     * Obtiene o almacena en cache
     */
    public static function remember($key, $callback, $ttl = 3600)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Obtiene o almacena en cache con tags
     */
    public static function rememberWithTags($key, $tags, $callback, $ttl = 3600)
    {
        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Limpia cache por tags
     */
    public static function clearByTags($tags)
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Genera clave de cache
     */
    public static function generateKey($prefix, $params = [])
    {
        $key = $prefix;

        if (!empty($params)) {
            $key .= '_' . md5(serialize($params));
        }

        return $key;
    }
}

// Uso
$users = CacheHelper::remember('active_users', function () {
    return User::where('status', 'active')->get();
}, 1800);

$userPosts = CacheHelper::rememberWithTags(
    "user_{$userId}_posts",
    ['users', "user_{$userId}"],
    function () use ($userId) {
        return User::find($userId)->posts()->get();
    }
);
```

### 3. Helpers de Logging

```php
// app/Helpers/LogHelper.php
class LogHelper
{
    /**
     * Log de información
     */
    public static function info($message, $context = [])
    {
        Log::info($message, $context);
    }

    /**
     * Log de error
     */
    public static function error($message, $context = [])
    {
        Log::error($message, $context);
    }

    /**
     * Log de warning
     */
    public static function warning($message, $context = [])
    {
        Log::warning($message, $context);
    }

    /**
     * Log de debug
     */
    public static function debug($message, $context = [])
    {
        Log::debug($message, $context);
    }

    /**
     * Log de actividad del usuario
     */
    public static function userActivity($action, $userId = null)
    {
        $userId = $userId ?: auth()->id();

        self::info("User activity: {$action}", [
            'user_id' => $userId,
            'action' => $action,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}

// Uso
LogHelper::info('User logged in', ['user_id' => $user->id]);
LogHelper::error('Payment failed', ['order_id' => $order->id]);
LogHelper::userActivity('created_post');
```

## Registro de Helpers

### 1. Autoload de Helpers

```php
// composer.json
{
    "autoload": {
        "files": [
            "app/Helpers/functions.php"
        ]
    }
}
```

### 2. Archivo de Funciones Globales

```php
// app/Helpers/functions.php
<?php

if (!function_exists('format_currency')) {
    function format_currency($amount, $currency = 'USD')
    {
        return FormatHelper::formatCurrency($amount, $currency);
    }
}

if (!function_exists('generate_slug')) {
    function generate_slug($text)
    {
        return StringHelper::generateSlug($text);
    }
}

if (!function_exists('format_date')) {
    function format_date($date, $format = 'd/m/Y H:i')
    {
        return DateHelper::formatForDisplay($date, $format);
    }
}

if (!function_exists('api_success')) {
    function api_success($data = null, $message = 'Success', $code = 200)
    {
        return ApiHelper::success($data, $message, $code);
    }
}

if (!function_exists('api_error')) {
    function api_error($message = 'Error', $code = 400, $errors = null)
    {
        return ApiHelper::error($message, $code, $errors);
    }
}
```

### 3. Service Provider para Helpers

```php
// app/Providers/HelperServiceProvider.php
class HelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('string.helper', function () {
            return new StringHelper();
        });

        $this->app->singleton('date.helper', function () {
            return new DateHelper();
        });

        $this->app->singleton('format.helper', function () {
            return new FormatHelper();
        });
    }

    public function boot()
    {
        // Cargar archivo de funciones
        require_once app_path('Helpers/functions.php');
    }
}
```

## Mejores Prácticas

### 1. Mantener Helpers Simples

```php
// ✅ BUENO - Helper simple y enfocado
class StringHelper
{
    public static function generateSlug($text)
    {
        return Str::slug($text);
    }
}

// ❌ MALO - Helper complejo
class StringHelper
{
    public static function processText($text, $options = [])
    {
        // 50+ líneas de lógica compleja
    }
}
```

### 2. Usar Nombres Descriptivos

```php
// ✅ BUENO - Nombres descriptivos
class DateHelper
{
    public static function formatForDisplay($date)
    public static function calculateAge($birthDate)
    public static function timeAgo($date)
}

// ❌ MALO - Nombres poco descriptivos
class DateHelper
{
    public static function format($date)
    public static function calculate($date)
    public static function ago($date)
}
```

### 3. Documentar Helpers

```php
/**
 * Helper para formateo de fechas
 */
class DateHelper
{
    /**
     * Formatea fecha para mostrar en la interfaz
     *
     * @param string|Carbon $date Fecha a formatear
     * @param string $format Formato de salida
     * @return string|null Fecha formateada o null si es inválida
     */
    public static function formatForDisplay($date, $format = 'd/m/Y H:i')
    {
        if (!$date) return null;

        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $date->format($format);
    }
}
```

### 4. Evitar Dependencias Complejas

```php
// ✅ BUENO - Sin dependencias complejas
class StringHelper
{
    public static function generateSlug($text)
    {
        return Str::slug($text);
    }
}

// ❌ MALO - Dependencias complejas
class StringHelper
{
    public static function generateSlug($text)
    {
        return app(SomeComplexService::class)->process($text);
    }
}
```

### 5. Usar Funciones Globales para Helpers Comunes

```php
// ✅ BUENO - Función global simple
function format_currency($amount)
{
    return '$' . number_format($amount, 2);
}

// Uso
$price = format_currency(1234.56);
```

Los helpers son herramientas poderosas para mantener el código limpio y reutilizable, pero deben usarse con moderación y siguiendo las mejores prácticas.
