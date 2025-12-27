# Laravel MVP Quick Reference Guide (Repo Reality)

**As-of:** December 26, 2025 — 7:27 PM (America/Chicago)

**Repo stack (origin/main):**
- Laravel `^12.0` (PHP `^8.2`)
- Jetstream `^5.4` (Inertia stack)
- Inertia `^2.0` + Vue 3 (`@inertiajs/vue3`)
- Sanctum `^4.0`
- `laravel/mcp` `^0.5.1`

## Quick Start Commands (Existing App)

### Bring up the Laravel app
```bash
cd laravel

# one-time install
composer install
npm install

# app setup (creates .env if missing, generates key, migrates, builds)
composer run setup

# run dev services (PHP server + queue + logs + Vite)
composer run dev
```

### Common artisan commands
```bash
cd laravel

php artisan migrate
php artisan route:list
php artisan tinker

# built-in health route (configured in bootstrap/app.php)
curl -i http://localhost:8000/up
```

## Key Files to Modify

### 1. User Model
```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
}
```

Note: On `origin/main`, `HasApiTokens` is already applied to `App\Models\User`.

### 2. API Routes
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    // Example: open endpoint
    Route::get('dioceses/search', [DioceseController::class, 'search']);

    // Example: protect API endpoints with token auth
    Route::middleware('auth:sanctum')->group(function () {
        // Route::post('tokens', [TokenController::class, 'store']);
    });
});
```

### 3. Auth Controller
```php
// Example token issuance pattern (Sanctum)
public function store(Request $request)
{
    $validated = $request->validate([
        'token_name' => ['required', 'string', 'max:100'],
    ]);

    $token = $request->user()->createToken($validated['token_name']);

    return response()->json([
        'token' => $token->plainTextToken,
    ]);
}
```

## MCP Endpoint (laravel/mcp)

The Laravel app registers a public MCP server and exposes it at:

```text
/mcp/public
```

To see what tools are available, check `App\Mcp\Servers\PublicServer`.

## Caddy Configuration

### Development (Self-Signed)
```caddyfile
localhost:8000 {
    root * /path/to/laravel/public
    php_fastcgi unix:/var/run/php/php8.2-fpm.sock
    file_server
}
```

### Production (with Domain)
```caddyfile
drm-catholic.example.com {
    root * /var/www/laravel-drm/public
    php_fastcgi unix:/var/run/php/php8.2-fpm.sock
    file_server
    encode gzip
}
```

### Testing (nip.io)
```caddyfile
192.168.1.100.nip.io {
    root * /var/www/laravel-drm/public
    php_fastcgi unix:/var/run/php/php8.2-fpm.sock
    file_server
}
```

## Critical Edge Cases

### 1. External Diocese Contacts
```php
// Migration
$table->foreignId('diocese_id')->nullable()->constrained();
$table->foreignId('home_diocese_id')->nullable()->constrained('dioceses');
$table->boolean('is_external_contact')->default(false);
```

### 2. Multiple Positions
```php
// Contact Model
public function positions() {
    return $this->hasMany(ContactPosition::class);
}

// ContactPosition Model
public function contact() {
    return $this->belongsTo(Contact::class);
}
```

### 3. Organization Scope
```php
// Migration
$table->enum('scope', ['parish', 'diocesan', 'provincial', 'national', 'international']);
$table->foreignId('diocese_id')->nullable()->constrained();
```

### 4. Polymorphic Contact Associations
```php
// Option 1: Nullable Foreign Keys
$table->foreignId('diocese_id')->nullable();
$table->foreignId('parish_id')->nullable();
$table->foreignId('school_id')->nullable();

// Option 2: Polymorphic
$table->morphs('contactable'); // Creates contactable_type, contactable_id
```

### 5. Address as JSON
```php
// Migration
$table->json('address')->nullable();

// Model Cast
protected $casts = [
    'address' => 'array',
];
```

## API Response Format

### Success Response
```php
return response()->json([
    // Keep response shapes stable for clients.
    'success' => true,
    'data' => $resource,
    'count' => $collection->count(), // for list endpoints
]);
```

### Error Response
```php
return response()->json([
    'success' => false,
    'error' => 'Error message',
], 404);
```

## Testing with Postman

### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

### Protected Route
```http
GET /api/dioceses
Authorization: Bearer {your-token-here}
```

## Common Query Patterns

### Filter by Diocese
```php
Route::get('parishes', function(Request $request) {
    $query = Parish::query();
    
    if ($request->has('dioceseId')) {
        $query->where('diocese_id', $request->dioceseId);
    }
    
    return response()->json([
        'success' => true,
        'data' => $query->get(),
    ]);
});
```

### Nested Resources
```php
Route::get('dioceses/{diocese}/parishes', function(Diocese $diocese) {
    return response()->json([
        'success' => true,
        'data' => $diocese->parishes,
        'count' => $diocese->parishes->count(),
    ]);
});
```

## Database Indexes

```php
// Critical indexes for performance
$table->index('diocese_id');
$table->index('state');
$table->index(['diocese_id', 'type']);
```

## Environment Variables

```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=drm_catholic
DB_USERNAME=postgres
DB_PASSWORD=secret

# App
APP_ENV=production
APP_DEBUG=false
APP_URL=https://drm-catholic.example.com

# Sanctum
SANCTUM_STATEFUL_DOMAINS=drm-catholic.example.com

# Session (use database or redis for production)
SESSION_DRIVER=database
```

## Notes

- This repo already uses Jetstream + Inertia (Vue 3). Prefer extending existing auth and UI rather than re-scaffolding.
- For API clients, Sanctum can authenticate requests via cookies (first-party) or Bearer tokens (programmatic).

## Security Checklist

- [ ] Use HTTPS in production
- [ ] Configure CORS properly
- [ ] Implement rate limiting
- [ ] Validate all inputs
- [ ] Use prepared statements (Eloquent does this)
- [ ] Set secure session cookies
- [ ] Implement CSRF protection for first-party UI requests
- [ ] Use environment variables for secrets
- [ ] Implement token expiration
- [ ] Add logging for authentication events

## Production Deployment Checklist

- [ ] Acquire domain name (or use nip.io for testing)
- [ ] Install PHP 8.2+
- [ ] Install Composer
- [ ] Install Node.js & npm
- [ ] Install PostgreSQL
- [ ] Install Caddy
- [ ] Clone repository
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm install && npm run build`
- [ ] Configure `.env` file
- [ ] Run `php artisan key:generate`
- [ ] Run `php artisan migrate --force`
- [ ] Configure Caddyfile
- [ ] Start Caddy
- [ ] Test HTTPS certificate
- [ ] Configure firewall (allow 80, 443)

## Useful Commands

```bash
# Generate controller
php artisan make:controller Api/DiocesesController --api

# Generate model with migration
php artisan make:model Diocese -m

# Generate API resource
php artisan make:resource DiocesesResource

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# List routes
php artisan route:list
```

## Troubleshooting

### "CSRF token mismatch"
- Add domain to `SANCTUM_STATEFUL_DOMAINS`
- Ensure cookies are being sent with requests

### "Unauthenticated"
- Check token is sent in Authorization header
- Verify token hasn't expired
- Ensure route uses `auth:sanctum` middleware

### Caddy certificate errors
- Check domain DNS resolves correctly
- Ensure ports 80/443 are accessible
- For IP-only: use self-signed or nip.io

### Database connection errors
- Verify credentials in `.env`
- Check database service is running
- Test connection with `php artisan tinker`
