# Controladores en Laravel

## Introducción

Los Controladores en Laravel son clases que manejan las peticiones HTTP y coordinan la lógica de la aplicación. Actúan como intermediarios entre las rutas y los modelos, organizando la lógica de negocio y devolviendo las respuestas apropiadas.

## 1. Estructura de Controladores

### Estructura Básica de un Controlador

```php
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Services\UserService;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->middleware('auth')->except(['index', 'show']);
        $this->middleware('admin')->only(['destroy']);
    }

    /**
     * Mostrar lista de usuarios
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getUsers($request->all());

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'message' => 'Formulario de creación de usuario'
        ]);
    }

    /**
     * Guardar nuevo usuario
     */
    public function store(UserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $user
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mostrar usuario específico
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->getUser($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(int $id): JsonResponse
    {
        $user = $this->userService->getUser($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Actualizar usuario
     */
    public function update(UserRequest $request, int $id): JsonResponse
    {
        try {
            $user = $this->userService->updateUser($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Eliminar usuario
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->deleteUser($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
```

### Crear Controlador con Artisan

```bash
# Crear controlador básico
php artisan make:controller UserController

# Crear controlador con métodos resource
php artisan make:controller UserController --resource

# Crear controlador con métodos resource y modelo
php artisan make:controller UserController --resource --model=User

# Crear controlador API
php artisan make:controller Api/UserController --api

# Crear controlador invokable (un solo método)
php artisan make:controller WelcomeController --invokable
```

## 2. Métodos de Acción

### Métodos Resource Estándar

```php
class PostController extends Controller
{
    /**
     * index() - Listar todos los recursos
     */
    public function index(Request $request)
    {
        $posts = Post::with(['user', 'comments'])
                    ->when($request->search, function ($query, $search) {
                        $query->where('title', 'LIKE', "%{$search}%");
                    })
                    ->when($request->status, function ($query, $status) {
                        $query->where('status', $status);
                    })
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * create() - Mostrar formulario de creación
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'tags' => $tags
            ]
        ]);
    }

    /**
     * store() - Guardar nuevo recurso
     */
    public function store(PostRequest $request)
    {
        DB::beginTransaction();

        try {
            $post = Post::create($request->validated());

            if ($request->has('tags')) {
                $post->tags()->attach($request->tags);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post creado exitosamente',
                'data' => $post->load(['user', 'tags'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el post'
            ], 500);
        }
    }

    /**
     * show() - Mostrar recurso específico
     */
    public function show(int $id)
    {
        $post = Post::with(['user', 'comments.user', 'tags'])
                   ->findOrFail($id);

        // Incrementar contador de vistas
        $post->increment('views_count');

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    /**
     * edit() - Mostrar formulario de edición
     */
    public function edit(int $id)
    {
        $post = Post::with(['tags'])->findOrFail($id);
        $categories = Category::all();
        $tags = Tag::all();

        return response()->json([
            'success' => true,
            'data' => [
                'post' => $post,
                'categories' => $categories,
                'tags' => $tags
            ]
        ]);
    }

    /**
     * update() - Actualizar recurso
     */
    public function update(PostRequest $request, int $id)
    {
        $post = Post::findOrFail($id);

        DB::beginTransaction();

        try {
            $post->update($request->validated());

            if ($request->has('tags')) {
                $post->tags()->sync($request->tags);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post actualizado exitosamente',
                'data' => $post->load(['user', 'tags'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el post'
            ], 500);
        }
    }

    /**
     * destroy() - Eliminar recurso
     */
    public function destroy(int $id)
    {
        $post = Post::findOrFail($id);

        try {
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el post'
            ], 500);
        }
    }
}
```

### Métodos Personalizados

```php
class UserController extends Controller
{
    /**
     * Método para buscar usuarios
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        $limit = $request->get('limit', 10);

        $users = User::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->limit($limit)
                    ->get(['id', 'name', 'email']);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Método para obtener estadísticas
     */
    public function stats()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Método para cambiar estado de usuario
     */
    public function toggleStatus(int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Estado del usuario actualizado',
            'data' => [
                'id' => $user->id,
                'is_active' => $user->is_active
            ]
        ]);
    }

    /**
     * Método para enviar email de bienvenida
     */
    public function sendWelcomeEmail(int $id)
    {
        $user = User::findOrFail($id);

        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));

            return response()->json([
                'success' => true,
                'message' => 'Email de bienvenida enviado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar email'
            ], 500);
        }
    }
}
```

## 3. Inyección de Dependencias

### Inyección en Constructor

```php
class PostController extends Controller
{
    private $postService;
    private $userService;
    private $emailService;

    public function __construct(
        PostService $postService,
        UserService $userService,
        EmailService $emailService
    ) {
        $this->postService = $postService;
        $this->userService = $userService;
        $this->emailService = $emailService;

        // Middleware específico
        $this->middleware('auth')->except(['index', 'show']);
        $this->middleware('can:edit,post')->only(['edit', 'update']);
        $this->middleware('can:delete,post')->only(['destroy']);
    }

    public function store(PostRequest $request)
    {
        $post = $this->postService->createPost($request->validated());

        // Notificar a seguidores
        $this->emailService->notifyFollowers($post);

        return response()->json([
            'success' => true,
            'data' => $post
        ], 201);
    }
}
```

### Inyección en Métodos

```php
class CommentController extends Controller
{
    public function store(
        CommentRequest $request,
        Post $post,
        CommentService $commentService,
        NotificationService $notificationService
    ) {
        $comment = $commentService->createComment($post, $request->validated());

        // Notificar al autor del post
        $notificationService->notifyPostAuthor($post, $comment);

        return response()->json([
            'success' => true,
            'data' => $comment->load('user')
        ], 201);
    }

    public function update(
        CommentRequest $request,
        Comment $comment,
        CommentService $commentService
    ) {
        $this->authorize('update', $comment);

        $comment = $commentService->updateComment($comment, $request->validated());

        return response()->json([
            'success' => true,
            'data' => $comment
        ]);
    }
}
```

## 4. Resource Controllers

### Controlador Resource Completo

```php
class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'price_min', 'price_max', 'search']);
        $products = $this->productService->getProducts($filters);

        return ProductResource::collection($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();

        return response()->json([
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());

        return (new ProductResource($product))
                ->response()
                ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'reviews.user']);

        return new ProductResource($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::all();
        $brands = Brand::all();

        return response()->json([
            'product' => new ProductResource($product),
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $product = $this->productService->updateProduct($product, $request->validated());

        return new ProductResource($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return response()->json([
            'message' => 'Producto eliminado exitosamente'
        ]);
    }
}
```

### Rutas Resource

```php
// routes/api.php
Route::apiResource('products', ProductController::class);

// Rutas equivalentes:
// GET /products - index()
// POST /products - store()
// GET /products/{product} - show()
// PUT/PATCH /products/{product} - update()
// DELETE /products/{product} - destroy()

// Excluir métodos específicos
Route::apiResource('products', ProductController::class)->except(['destroy']);

// Solo métodos específicos
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Rutas con middleware
Route::apiResource('products', ProductController::class)
     ->middleware(['auth:sanctum', 'verified']);
```

## 5. API Controllers

### Controlador API Especializado

```php
class ApiUserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Listar usuarios con paginación
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $status = $request->get('status');

        $users = User::query()
                    ->when($search, function ($query, $search) {
                        $query->where('name', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->when($status, function ($query, $status) {
                        $query->where('status', $status);
                    })
                    ->with(['profile', 'posts'])
                    ->paginate($perPage);

        return UserResource::collection($users);
    }

    /**
     * Crear usuario
     */
    public function store(UserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return (new UserResource($user))
                ->response()
                ->setStatusCode(201);
    }

    /**
     * Mostrar usuario
     */
    public function show(User $user)
    {
        $user->load(['profile', 'posts', 'comments']);

        return new UserResource($user);
    }

    /**
     * Actualizar usuario
     */
    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $user = $this->userService->updateUser($user, $request->validated());

        return new UserResource($user);
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $this->userService->deleteUser($user);

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    /**
     * Obtener posts del usuario
     */
    public function posts(User $user, Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $posts = $user->posts()
                     ->with(['comments', 'tags'])
                     ->orderBy('created_at', 'desc')
                     ->paginate($perPage);

        return PostResource::collection($posts);
    }

    /**
     * Seguir usuario
     */
    public function follow(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->id === $user->id) {
            return response()->json([
                'message' => 'No puedes seguirte a ti mismo'
            ], 400);
        }

        $currentUser->following()->attach($user->id);

        return response()->json([
            'message' => 'Usuario seguido exitosamente'
        ]);
    }

    /**
     * Dejar de seguir usuario
     */
    public function unfollow(User $user)
    {
        $currentUser = auth()->user();
        $currentUser->following()->detach($user->id);

        return response()->json([
            'message' => 'Dejaste de seguir al usuario'
        ]);
    }
}
```

## 6. Controladores Invokables

### Controlador con un Solo Método

```php
class WelcomeController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'total_users' => User::count(),
            'total_posts' => Post::count(),
            'total_comments' => Comment::count()
        ];

        return response()->json([
            'message' => 'Bienvenido a la API',
            'stats' => $stats
        ]);
    }
}

// Uso en rutas
Route::get('/', WelcomeController::class);
```

### Controlador para Acciones Específicas

```php
class ProcessPaymentController extends Controller
{
    private $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->middleware('auth:sanctum');
    }

    public function __invoke(PaymentRequest $request)
    {
        try {
            $payment = $this->paymentService->processPayment($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Pago procesado exitosamente',
                'data' => $payment
            ]);

        } catch (PaymentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

// Uso en rutas
Route::post('/process-payment', ProcessPaymentController::class);
```

## 7. Controladores con Traits

### Traits Comunes para Controladores

```php
trait ApiResponseTrait
{
    protected function successResponse($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function errorResponse($message, $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $code);
    }

    protected function createdResponse($data, $message = 'Recurso creado exitosamente')
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function noContentResponse()
    {
        return response()->json([], 204);
    }
}

trait ValidationTrait
{
    protected function validateAndRespond($request, $rules, $messages = [])
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 422);
        }

        return null; // No hay errores
    }
}

// Uso en controlador
class UserController extends Controller
{
    use ApiResponseTrait, ValidationTrait;

    public function store(Request $request)
    {
        $validation = $this->validateAndRespond($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        if ($validation) {
            return $validation;
        }

        $user = User::create($request->all());

        return $this->createdResponse($user);
    }
}
```

## 8. Controladores con Middleware

### Middleware en Controladores

```php
class AdminController extends Controller
{
    public function __construct()
    {
        // Aplicar middleware a todos los métodos
        $this->middleware('auth:sanctum');
        $this->middleware('admin');

        // Aplicar middleware solo a métodos específicos
        $this->middleware('can:manage-users')->only(['index', 'store', 'update', 'destroy']);
        $this->middleware('can:view-reports')->only(['reports']);

        // Aplicar middleware excepto a ciertos métodos
        $this->middleware('throttle:60,1')->except(['index']);
    }

    public function index()
    {
        $users = User::with(['profile'])->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function reports()
    {
        $reports = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }
}
```

## 9. Controladores con Autorización

### Autorización en Controladores

```php
class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(PostRequest $request)
    {
        // Verificar si el usuario puede crear posts
        $this->authorize('create', Post::class);

        $post = Post::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $post
        ], 201);
    }

    public function update(PostRequest $request, Post $post)
    {
        // Verificar si el usuario puede actualizar este post
        $this->authorize('update', $post);

        $post->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    public function destroy(Post $post)
    {
        // Verificar si el usuario puede eliminar este post
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post eliminado exitosamente'
        ]);
    }

    public function publish(Post $post)
    {
        // Verificar si el usuario puede publicar este post
        $this->authorize('publish', $post);

        $post->update(['status' => 'published', 'published_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Post publicado exitosamente'
        ]);
    }
}
```

## 10. Mejores Prácticas

### 1. Separación de Responsabilidades

```php
// ❌ Mal - Lógica de negocio en el controlador
class UserController extends Controller
{
    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        // Lógica de negocio
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Enviar email
        Mail::to($user->email)->send(new WelcomeEmail($user));

        return response()->json($user, 201);
    }
}

// ✅ Bien - Separación de responsabilidades
class UserController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(UserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());

        return response()->json($user, 201);
    }
}
```

### 2. Manejo de Errores

```php
class PostController extends Controller
{
    public function store(PostRequest $request)
    {
        try {
            DB::beginTransaction();

            $post = Post::create($request->validated());

            if ($request->has('tags')) {
                $post->tags()->attach($request->tags);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $post
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear post', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}
```

### 3. Documentación

```php
/**
 * Controlador para gestionar usuarios del sistema
 *
 * @package App\Http\Controllers
 * @author Tu Nombre
 * @version 1.0
 */
class UserController extends Controller
{
    /**
     * Mostrar lista de usuarios
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Implementación
    }

    /**
     * Crear nuevo usuario
     *
     * @param UserRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(UserRequest $request): JsonResponse
    {
        // Implementación
    }
}
```

## Conclusión

Los Controladores en Laravel son fundamentales para organizar la lógica de la aplicación y manejar las peticiones HTTP. Al seguir las mejores prácticas de separación de responsabilidades, inyección de dependencias y manejo de errores, puedes crear controladores mantenibles y escalables.
