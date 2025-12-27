# Laravel MVP Quick Reference Guide

**Laravel Version:** 11.5.0 (December 2024)  
**Sanctum Version:** v4.0.7  
**Breeze Version:** v2.3.0+  
**PHP:** 8.2, 8.3, or 8.4

## Quick Start Commands

### Laravel + Breeze Setup
```bash
# Create new Laravel project
composer create-project laravel/laravel drm-catholic-laravel

# Install Breeze
composer require laravel/breeze --dev
php artisan breeze:install blade

# Install dependencies and migrate
npm install && npm run dev
php artisan migrate
```

### Sanctum API Setup
```bash
# Install Sanctum (Laravel 11+)
php artisan install:api

# Or manually
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
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

### 2. API Routes
```php
// routes/api.php
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('dioceses', DiocesesController::class);
});
```

### 3. Auth Controller
```php
// app/Http/Controllers/Api/AuthController.php
public function login(Request $request) {
    $user = User::where('email', $request->email)->first();
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    return response()->json([
        'token' => $user->createToken('api-token')->plainTextToken
    ]);
}
```

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
    'success' => true,
    'data' => $resource,
    'count' => $collection->count(), // for lists
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

## Laravel 11.5 New Features

```php
// Anonymous Event Broadcasting (11.5+)
use Illuminate\Support\Facades\Broadcast;

Broadcast::on('diocese-updates')->send([
    'message' => 'New parish added',
    'data' => $parish
]);

// Enhanced URL building with query parameters (11.5+)
$url = url()->query('/api/dioceses', [
    'state' => 'TX',
    'type' => 'diocese'
]);
```

## Security Checklist

- [ ] Use HTTPS in production
- [ ] Configure CORS properly
- [ ] Implement rate limiting
- [ ] Validate all inputs
- [ ] Use prepared statements (Eloquent does this)
- [ ] Set secure session cookies
- [ ] Implement CSRF protection (Breeze includes)
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
