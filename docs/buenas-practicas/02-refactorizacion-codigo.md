# Refactorización y Limpieza de Código en Laravel

## ¿Qué es la Refactorización?

La refactorización es el proceso de mejorar el código existente sin cambiar su comportamiento externo, haciéndolo más legible, mantenible y eficiente.

## Beneficios de la Refactorización

-   **Legibilidad**: Código más fácil de entender
-   **Mantenibilidad**: Más fácil de modificar y extender
-   **Reutilización**: Código más modular y reutilizable
-   **Testing**: Más fácil de probar
-   **Rendimiento**: Código más eficiente

## Principios de Refactorización

### 1. Principio DRY (Don't Repeat Yourself)

**Problema**: Código duplicado.

```php
// ❌ MALO - Código duplicado
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'sometimes|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($id);
        $user->update($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }
}
```

**Solución**: Extraer métodos comunes.

```php
// ✅ BUENO - Código refactorizado
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator = $this->validateUser($request->all());

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::create($request->validated());
        return $this->successResponse($user, 'User created successfully', 201);
    }

    public function update(Request $request, $id)
    {
        $validator = $this->validateUser($request->all(), $id);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::findOrFail($id);
        $user->update($request->validated());
        return $this->successResponse($user, 'User updated successfully');
    }

    private function validateUser($data, $userId = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'password' => $userId ? 'sometimes|min:6' : 'required|min:6'
        ];

        return Validator::make($data, $rules);
    }

    private function validationErrorResponse($errors)
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }

    private function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
```

### 2. Principio de Responsabilidad Única (SRP)

**Problema**: Clases con múltiples responsabilidades.

```php
// ❌ MALO - Múltiples responsabilidades
class UserService
{
    public function createUser($data)
    {
        // Validación
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Creación del usuario
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        // Envío de email
        Mail::to($user->email)->send(new WelcomeEmail($user));

        // Logging
        Log::info('User created', ['user_id' => $user->id]);

        // Cache
        Cache::forget('users_list');

        return $user;
    }
}
```

**Solución**: Separar responsabilidades.

```php
// ✅ BUENO - Responsabilidades separadas
class UserService
{
    private $validator;
    private $mailer;
    private $logger;
    private $cache;

    public function __construct(
        UserValidator $validator,
        UserMailer $mailer,
        UserLogger $logger,
        UserCache $cache
    ) {
        $this->validator = $validator;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    public function createUser($data)
    {
        $this->validator->validate($data);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $this->mailer->sendWelcomeEmail($user);
        $this->logger->logUserCreation($user);
        $this->cache->clearUsersList();

        return $user;
    }
}

class UserValidator
{
    public function validate($data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}

class UserMailer
{
    public function sendWelcomeEmail($user)
    {
        Mail::to($user->email)->send(new WelcomeEmail($user));
    }
}

class UserLogger
{
    public function logUserCreation($user)
    {
        Log::info('User created', ['user_id' => $user->id]);
    }
}

class UserCache
{
    public function clearUsersList()
    {
        Cache::forget('users_list');
    }
}
```

### 3. Extracción de Métodos

**Problema**: Métodos muy largos y complejos.

```php
// ❌ MALO - Método muy largo
class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->has('category')) {
            $query->where('category_id', $request->get('category'));
        }

        if ($request->has('user')) {
            $query->where('user_id', $request->get('user'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to'));
        }

        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $posts = $query->with(['user', 'category', 'tags'])
                      ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => 'Posts retrieved successfully'
        ]);
    }
}
```

**Solución**: Extraer métodos más pequeños.

```php
// ✅ BUENO - Métodos extraídos
class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::query();

        $this->applySearchFilter($query, $request);
        $this->applyCategoryFilter($query, $request);
        $this->applyUserFilter($query, $request);
        $this->applyStatusFilter($query, $request);
        $this->applyDateFilters($query, $request);
        $this->applySorting($query, $request);

        $posts = $query->with(['user', 'category', 'tags'])
                      ->paginate($request->get('per_page', 15));

        return $this->successResponse($posts, 'Posts retrieved successfully');
    }

    private function applySearchFilter($query, Request $request)
    {
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
    }

    private function applyCategoryFilter($query, Request $request)
    {
        if ($request->has('category')) {
            $query->where('category_id', $request->get('category'));
        }
    }

    private function applyUserFilter($query, Request $request)
    {
        if ($request->has('user')) {
            $query->where('user_id', $request->get('user'));
        }
    }

    private function applyStatusFilter($query, Request $request)
    {
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
    }

    private function applyDateFilters($query, Request $request)
    {
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to'));
        }
    }

    private function applySorting($query, Request $request)
    {
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    private function successResponse($data, $message = 'Success')
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ]);
    }
}
```

### 4. Uso de Traits para Funcionalidades Compartidas

**Problema**: Código duplicado entre controladores.

```php
// ❌ MALO - Código duplicado en controladores
class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(15);
        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User retrieved successfully'
        ]);
    }
}

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::paginate(15);
        return response()->json([
            'success' => true,
            'data' => $posts,
            'message' => 'Posts retrieved successfully'
        ]);
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $post,
            'message' => 'Post retrieved successfully'
        ]);
    }
}
```

**Solución**: Usar traits.

```php
// ✅ BUENO - Usando traits
trait ApiResponse
{
    protected function successResponse($data, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    protected function errorResponse($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    protected function notFoundResponse($message = 'Resource not found')
    {
        return $this->errorResponse($message, 404);
    }
}

class UserController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $users = User::paginate(15);
        return $this->successResponse($users, 'Users retrieved successfully');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->successResponse($user, 'User retrieved successfully');
    }
}

class PostController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $posts = Post::paginate(15);
        return $this->successResponse($posts, 'Posts retrieved successfully');
    }

    public function show($id)
    {
        $post = Post::findOrFail($id);
        return $this->successResponse($post, 'Post retrieved successfully');
    }
}
```

### 5. Uso de Form Requests para Validación

**Problema**: Validación en controladores.

```php
// ❌ MALO - Validación en controlador
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create($request->validated());
        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'User created successfully'
        ], 201);
    }
}
```

**Solución**: Usar Form Requests.

```php
// ✅ BUENO - Usando Form Requests
class CreateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El email es requerido',
            'email.unique' => 'El email ya está registrado',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ];
    }
}

class UserController extends Controller
{
    use ApiResponse;

    public function store(CreateUserRequest $request)
    {
        $user = User::create($request->validated());
        return $this->successResponse($user, 'User created successfully', 201);
    }
}
```

### 6. Uso de Resources para Transformación de Datos

**Problema**: Transformación manual de datos.

```php
// ❌ MALO - Transformación manual
class UserController extends Controller
{
    public function index()
    {
        $users = User::with('posts')->get();

        $transformedUsers = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'posts_count' => $user->posts->count(),
                'posts' => $user->posts->map(function($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'created_at' => $post->created_at->format('Y-m-d H:i:s')
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $transformedUsers
        ]);
    }
}
```

**Solución**: Usar Resources.

```php
// ✅ BUENO - Usando Resources
class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }
}

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'posts_count' => $this->posts_count ?? $this->posts->count(),
            'posts' => PostResource::collection($this->whenLoaded('posts'))
        ];
    }
}

class UserController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $users = User::with('posts')->get();
        return $this->successResponse(UserResource::collection($users));
    }
}
```

## Técnicas de Refactorización Avanzadas

### 1. Extracción de Clases de Servicio

```php
// Antes de refactorizar
class OrderController extends Controller
{
    public function processOrder(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Lógica de negocio compleja
        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => 0,
            'status' => 'pending'
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $subtotal = $product->price * $item['quantity'];
            $total += $subtotal;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $subtotal
            ]);
        }

        $order->update(['total' => $total]);

        // Procesamiento de pago
        if ($request->payment_method === 'credit_card') {
            // Lógica de tarjeta de crédito
        } elseif ($request->payment_method === 'paypal') {
            // Lógica de PayPal
        }

        // Envío de email
        Mail::to(auth()->user()->email)->send(new OrderConfirmation($order));

        return response()->json(['order' => $order]);
    }
}

// Después de refactorizar
class OrderController extends Controller
{
    private $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function processOrder(ProcessOrderRequest $request)
    {
        $order = $this->orderService->processOrder($request->validated());
        return response()->json(['order' => new OrderResource($order)]);
    }
}

class OrderService
{
    private $paymentProcessor;
    private $mailer;

    public function __construct(PaymentProcessor $paymentProcessor, OrderMailer $mailer)
    {
        $this->paymentProcessor = $paymentProcessor;
        $this->mailer = $mailer;
    }

    public function processOrder($data)
    {
        $order = $this->createOrder($data);
        $this->addOrderItems($order, $data['items']);
        $this->processPayment($order, $data['payment_method']);
        $this->mailer->sendOrderConfirmation($order);

        return $order;
    }

    private function createOrder($data)
    {
        return Order::create([
            'user_id' => auth()->id(),
            'total' => 0,
            'status' => 'pending'
        ]);
    }

    private function addOrderItems($order, $items)
    {
        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $subtotal = $product->price * $item['quantity'];
            $total += $subtotal;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $product->price,
                'subtotal' => $subtotal
            ]);
        }

        $order->update(['total' => $total]);
    }

    private function processPayment($order, $paymentMethod)
    {
        $this->paymentProcessor->process($order, $paymentMethod);
    }
}
```

### 2. Uso de Repositorios

```php
// Antes de refactorizar
class UserController extends Controller
{
    public function index()
    {
        $users = User::with('posts')
                    ->where('status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
        return response()->json($users);
    }

    public function findByEmail($email)
    {
        $user = User::where('email', $email)->first();
        return response()->json($user);
    }
}

// Después de refactorizar
class UserRepository
{
    public function getActiveUsers()
    {
        return User::with('posts')
                  ->where('status', 'active')
                  ->orderBy('created_at', 'desc')
                  ->paginate(15);
    }

    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }
}

class UserController extends Controller
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $users = $this->userRepository->getActiveUsers();
        return response()->json($users);
    }

    public function findByEmail($email)
    {
        $user = $this->userRepository->findByEmail($email);
        return response()->json($user);
    }
}
```

## Herramientas de Refactorización

### 1. IDE Helper

```bash
composer require --dev barryvdh/laravel-ide-helper
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta
```

### 2. PHP CS Fixer

```bash
composer require --dev friendsofphp/php-cs-fixer
```

### 3. Laravel Pint

```bash
composer require --dev laravel/pint
./vendor/bin/pint
```

## Mejores Prácticas

### 1. Mantener Métodos Pequeños

```php
// ✅ BUENO - Método pequeño y enfocado
public function calculateTotal($items)
{
    return $items->sum('price');
}

// ❌ MALO - Método muy largo
public function processOrder($data)
{
    // 50+ líneas de código
}
```

### 2. Usar Nombres Descriptivos

```php
// ✅ BUENO - Nombres descriptivos
public function getActiveUsersWithPosts()
public function calculateOrderTotal()
public function sendWelcomeEmail()

// ❌ MALO - Nombres poco descriptivos
public function getUsers()
public function calculate()
public function sendEmail()
```

### 3. Evitar Anidación Profunda

```php
// ✅ BUENO - Poca anidación
public function processUser($user)
{
    if (!$user->isActive()) {
        return false;
    }

    if (!$user->hasPermission('admin')) {
        return false;
    }

    return $this->performAction($user);
}

// ❌ MALO - Anidación profunda
public function processUser($user)
{
    if ($user->isActive()) {
        if ($user->hasPermission('admin')) {
            if ($user->isVerified()) {
                // Lógica aquí
            }
        }
    }
}
```

### 4. Usar Early Returns

```php
// ✅ BUENO - Early returns
public function processOrder($order)
{
    if (!$order->isValid()) {
        return false;
    }

    if (!$order->hasItems()) {
        return false;
    }

    return $this->processValidOrder($order);
}

// ❌ MALO - Sin early returns
public function processOrder($order)
{
    if ($order->isValid()) {
        if ($order->hasItems()) {
            return $this->processValidOrder($order);
        } else {
            return false;
        }
    } else {
        return false;
    }
}
```

La refactorización es un proceso continuo que mejora la calidad del código y facilita su mantenimiento a largo plazo.
