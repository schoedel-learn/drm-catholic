# Laravel MVP Research: Authentication + Caddy Deployment

## Executive Summary

This document provides research findings for implementing a Laravel MVP with authentication and Caddy-based deployment for the DRM Catholic application. The research covers Laravel scaffolding options, Sanctum token authentication, Caddy deployment constraints, and preservation considerations for the existing Node.js API contract.

**Laravel Version:** Laravel 11.5.0 (latest stable, released December 17, 2024)  
**PHP Compatibility:** PHP 8.2, 8.3, or 8.4  
**Package Versions:**
- Laravel Sanctum: v4.0.7 (December 17, 2024)
- Laravel Breeze: v2.3.0+ (compatible with Laravel 11 and Inertia V2)

---

## 1. Laravel Authentication Scaffolding

### Recommended Approach: Laravel Breeze

**Primary Recommendation:** Laravel Breeze
- **Why:** Minimal, highly customizable, modern stack with Blade or Inertia.js support
- **Pros:**
  - Simple login, registration, password reset out-of-the-box
  - Tailwind CSS styling (can be customized)
  - Clean codebase, easy to understand and modify
  - Perfect for learning and small-to-medium applications
  - Can work with Blade (server-rendered) or Inertia.js (SPA-like experience)
  
**Installation:**
```bash
composer require laravel/breeze --dev
php artisan breeze:install
npm install && npm run dev
php artisan migrate
```

**Options:**
- `blade` - Traditional Blade templates (recommended for MVP)
- `vue` - Vue.js with Inertia.js
- `react` - React with Inertia.js
- `api` - API-only (no frontend scaffolding)

### Alternative Options

#### Laravel UI (Bootstrap/Vue)
- **Use Case:** If Bootstrap styling is required
- **Installation:**
  ```bash
  composer require laravel/ui
  php artisan ui bootstrap --auth
  npm install && npm run dev
  php artisan migrate
  ```
- **Pros:** Familiar Bootstrap styling, good for traditional web apps
- **Cons:** Older approach, less modern than Breeze

#### Laravel Jetstream
- **Use Case:** Complex applications requiring teams, 2FA, etc.
- **Pros:** Advanced features (teams, 2FA, API tokens, session management)
- **Cons:** Overkill for MVP, more complex learning curve
- **Not recommended for MVP** - too feature-heavy

### Laravel 11.5 New Features (December 2024)

The latest Laravel 11.5.0 release includes several improvements relevant to this project:

1. **Anonymous Event Broadcasting** - Simplified real-time notifications using `Broadcast::on('channel')->send()` syntax, useful for live updates without creating full event classes
2. **Blade Performance Improvements** - Up to 20% faster rendering for components, improving page load times
3. **Enhanced URL Generation** - New `url()->query()` method for building URLs with query parameters (useful for filter/sort endpoints)
4. **Better Code Organization** - `make:trait` and `make:interface` commands now respect conventional namespaces (`App\Contracts`, `App\Traits`)

These features can be leveraged during implementation for better performance and developer experience.

### Security Best Practices

1. **CSRF Protection** - Laravel includes CSRF tokens automatically in forms
2. **Password Hashing** - Always use `Hash::make()`, never plain text
3. **Rate Limiting** - Implement login throttling to prevent brute-force attacks
4. **Session Security** - Configure `HttpOnly` cookies and use HTTPS in production
5. **Validation** - Validate all inputs server-side using Form Requests
6. **Remember Me** - Use Laravel's built-in secure "Remember Me" functionality

---

## 2. Laravel Sanctum API Authentication

### Overview

Laravel Sanctum provides lightweight API token authentication ideal for:
- SPAs (Single Page Applications)
- Mobile applications
- Simple token-based APIs
- Simpler than OAuth (Passport)

### Setup Steps

#### 1. Install Sanctum
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Or in Laravel 11+:
```bash
php artisan install:api
```

#### 2. Update User Model
```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
}
```

#### 3. Configure API Guard
```php
// config/auth.php
'guards' => [
    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],
```

#### 4. Middleware Configuration
```php
// app/Http/Kernel.php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

#### 5. API Routes Example
```php
// routes/api.php
use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Protected DRM Catholic API routes
    Route::apiResource('dioceses', DiocesesController::class);
    Route::apiResource('parishes', ParishesController::class);
    Route::apiResource('contacts', ContactsController::class);
});
```

#### 6. Auth Controller Implementation
```php
// app/Http/Controllers/Api/AuthController.php
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
```

### Token Usage

Client requests should include the token in the Authorization header:
```
Authorization: Bearer {token}
```

### Token Abilities/Scopes (Optional)

For granular permissions:
```php
$token = $user->createToken('api-token', ['dioceses:read', 'parishes:read'])->plainTextToken;
```

Checking abilities in controllers:
```php
if ($request->user()->tokenCan('dioceses:write')) {
    // Allow write operations
}
```

---

## 3. Caddy Deployment with IP-Only HTTPS

### Key Constraints

**Critical Limitation:** Caddy's automatic HTTPS with Let's Encrypt/ZeroSSL **requires a domain name**. Public Certificate Authorities (CAs) do not issue certificates for bare IP addresses.

### Deployment Options

#### Option A: Self-Signed Certificates (Development/Internal Use)
**When to use:** Internal networks, development, testing

**Caddyfile Configuration:**
```
192.168.1.100 {
    root * /var/www/laravel-drm/public
    php_fastcgi unix//var/run/php/php8.2-fpm.sock
    file_server
    encode gzip
}
```

**Characteristics:**
- ✅ Caddy automatically generates self-signed certificate
- ✅ HTTPS works immediately
- ❌ Browser certificate warnings
- ❌ Requires manual CA trust installation on all clients
- **Best for:** Development, internal-only deployments

**Installing Caddy's Root CA on Clients:**
```bash
# Export Caddy's root certificate
caddy trust export --output caddy-root.crt

# Distribute to clients and install in their trust stores
```

#### Option B: Custom Certificate from Internal CA
**When to use:** Enterprise environments with internal PKI

**Caddyfile Configuration:**
```
192.168.1.100 {
    tls /etc/caddy/certs/server.crt /etc/caddy/certs/server.key
    root * /var/www/laravel-drm/public
    php_fastcgi unix//var/run/php/php8.2-fpm.sock
    file_server
}
```

**Characteristics:**
- ✅ Use organization's internal CA
- ✅ No self-signed warnings if CA is trusted
- ❌ Requires certificate generation from internal CA
- ❌ CA must be distributed to all clients
- **Best for:** Corporate/enterprise internal deployments

#### Option C: Domain with nip.io/xip.io (Production Workaround)
**When to use:** Production-like deployment without purchasing domain

**Concept:** Services like nip.io provide wildcard DNS that resolves to IPs:
- `192.168.1.100.nip.io` → resolves to `192.168.1.100`
- `10-0-0-5.nip.io` → resolves to `10.0.0.5`

**Caddyfile Configuration:**
```
192.168.1.100.nip.io {
    root * /var/www/laravel-drm/public
    php_fastcgi unix//var/run/php/php8.2-fpm.sock
    file_server
    encode gzip
}
```

**Characteristics:**
- ✅ Real Let's Encrypt certificate (trusted by browsers)
- ✅ No certificate warnings
- ✅ Works with ACME HTTP-01 challenge
- ❌ Requires internet access for ACME validation
- ❌ Depends on third-party DNS service
- **Best for:** Testing production deployment without domain

#### Option D: Real Domain (Recommended for Production)
**When to use:** Actual production deployment

**Caddyfile Configuration:**
```
drm-catholic.example.com {
    root * /var/www/laravel-drm/public
    php_fastcgi unix//var/run/php/php8.2-fpm.sock
    file_server
    encode gzip
    
    # Laravel-specific settings
    @notStatic {
        not path /css/* /js/* /images/*
        file {
            try_files {path} /index.php?{query}
        }
    }
    rewrite @notStatic /index.php
}
```

**Characteristics:**
- ✅ Fully trusted certificate
- ✅ Caddy handles auto-renewal
- ✅ Production-ready
- **Required:** Domain name ownership

### Recommended Deployment Strategy

1. **Development/Testing:** Option A (self-signed)
2. **Internal Production (no internet):** Option B (internal CA)
3. **Production Testing (internet):** Option C (nip.io)
4. **Production:** Option D (real domain) **← Strongly recommended**

### Complete Caddy + Laravel Configuration

```caddyfile
# /etc/caddy/Caddyfile
{
    email admin@example.com
}

your-domain.com {
    root * /var/www/laravel-drm/public
    
    # PHP-FPM
    php_fastcgi unix//var/run/php/php8.2-fpm.sock {
        env APP_ENV production
        env APP_DEBUG false
    }
    
    # Laravel index.php handler
    try_files {path} /index.php?{query}
    
    # File server for static assets
    file_server
    
    # Compression
    encode gzip zstd
    
    # Security headers
    header {
        X-Frame-Options "SAMEORIGIN"
        X-Content-Type-Options "nosniff"
        Referrer-Policy "strict-origin-when-cross-origin"
        Permissions-Policy "geolocation=(), microphone=(), camera=()"
    }
    
    # Logging
    log {
        output file /var/log/caddy/drm-catholic.log
        format json
    }
}
```

---

## 4. Node.js API Contract Preservation

### Existing API Overview

The current Node.js/Express API provides a comprehensive RESTful interface for Catholic diocesan management with the following key entities:

#### Core Resources
- **Dioceses** (`/api/v1/dioceses`)
- **Regions** (`/api/v1/regions`)
- **Deaneries** (`/api/v1/deaneries`)
- **Parishes** (`/api/v1/parishes`)
- **Missions** (`/api/v1/missions`)
- **Schools** (`/api/v1/schools`)
- **Religious Houses** (`/api/v1/religious-houses`)
- **Organizations** (`/api/v1/organizations`)
- **Apostolates** (`/api/v1/apostolates`)
- **Diocesan Offices** (`/api/v1/offices`)
- **Contacts** (`/api/v1/contacts`)
- **Positions** (`/api/v1/positions`)

### Critical Edge Cases to Preserve

#### 1. External Diocese Tracking
**Issue:** Contacts can belong to different dioceses (cross-diocesan tracking)
```typescript
interface Contact {
  dioceseId?: string;        // Diocese where contact serves
  homeDioceseId?: string;    // Contact's home/incardinated diocese
  isExternalContact?: boolean;
}
```
**Laravel Implementation:** Must support nullable foreign keys and boolean flags

#### 2. Multiple Positions per Contact
**Issue:** A single contact can hold multiple simultaneous roles (e.g., bishop + USCCB committee member)
```typescript
interface Contact {
  role: ContactRole;              // Primary role
  additionalPositions?: ContactPosition[];
}

interface ContactPosition {
  contactId: string;
  role: ContactRole;
  dioceseId?: string;
  organizationId?: string;
  // ... other entity IDs
}
```
**Laravel Implementation:** 
- One-to-many `Contact -> ContactPosition`
- Polymorphic relationships for position entities
- Consider using `morphTo` for flexible entity associations

#### 3. Organization Scope Levels
**Issue:** Organizations exist at different hierarchical levels
```typescript
type OrganizationScope = 
  | 'parish'        // Parish-level
  | 'diocesan'      // Diocese-level
  | 'provincial'    // Province-level
  | 'national'      // National (e.g., USCCB)
  | 'international' // International (e.g., Roman Curia)
```
**Laravel Implementation:**
- Enum or string field
- Nullable `dioceseId` for national/international organizations
- Special handling for USCCB and Vatican entities

#### 4. SVDP Hierarchy
**Issue:** St. Vincent de Paul Society has multi-level structure
```typescript
type SVDPLevel =
  | 'national_council'
  | 'regional_council'
  | 'district_council'
  | 'diocesan_council'
  | 'conference'  // Parish level
```
**Laravel Implementation:**
- Self-referential `parentOrganizationId`
- Separate `svdpLevel` field
- Tree structure queries (nested sets or closure table)

#### 5. Sacred Site Designations
**Issue:** Parishes can have special designations (cathedral, basilica, shrine)
```typescript
interface Parish {
  isCathedral?: boolean;
  isBasilica?: boolean;
  isShrine?: boolean;
  basilicaType?: 'major' | 'minor';
  shrineType?: 'national' | 'diocesan' | 'international';
  sacredSiteDesignation?: SacredSiteDesignation;
}
```
**Laravel Implementation:**
- Boolean flags with nullable type fields
- Validation: only one major sacred designation per parish

#### 6. Optional Hierarchical Links
**Issue:** Flexible hierarchy - parishes may or may not be in regions/deaneries
```typescript
interface Parish {
  dioceseId: string;      // Required
  regionId?: string;      // Optional
  deaneryId?: string;     // Optional
}
```
**Laravel Implementation:**
- Nullable foreign keys
- Cascade/set null deletion policies
- Flexible query scopes

#### 7. Polymorphic Contact Associations
**Issue:** Contacts can be associated with many different entity types
```typescript
interface Contact {
  dioceseId?: string;
  parishId?: string;
  schoolId?: string;
  organizationId?: string;
  apostolateId?: string;
  officeId?: string;
  religiousHouseId?: string;
  missionId?: string;
}
```
**Laravel Implementation Options:**
1. **Nullable foreign keys** (current Node approach)
2. **Polymorphic relationship** (`contactable_type`, `contactable_id`)
3. **Hybrid approach:** Primary position as foreign key + polymorphic for additional

#### 8. Address Reusability
**Issue:** Common Address interface used across multiple entities
```typescript
interface Address {
  street: string;
  city: string;
  state: string;
  zipCode: string;
  country: string;
}
```
**Laravel Implementation:**
- JSON column approach (simple, denormalized)
- Separate `addresses` table with morph (normalized, reusable)
- Cast to Value Object in Eloquent models

### API Response Format Preservation

All endpoints currently return this format:
```json
{
  "success": true|false,
  "data": {...} or [...],
  "count": 10,  // For list endpoints
  "error": "message"  // For errors
}
```

**Laravel Implementation:**
- API Resource classes for consistent formatting
- Custom error handler for consistent error responses
- Middleware to wrap all responses

### Query Parameters to Support

#### Common Filters
- `dioceseId` - Filter by diocese
- `state` - Filter by state
- `type` - Filter by entity type
- `role` - Filter contacts by role
- `scope` - Filter organizations by scope

#### Pagination
Current API doesn't explicitly paginate - consider adding:
```
?page=1&perPage=50
```

### Nested Routes to Preserve

Pattern: `/api/v1/{parent}/{id}/{child}`

Examples:
- `/api/v1/dioceses/:id/parishes` - Parishes in diocese
- `/api/v1/dioceses/:id/contacts` - Contacts in diocese
- `/api/v1/parishes/:id/contacts` - Contacts in parish
- `/api/v1/regions/:id/deaneries` - Deaneries in region

**Laravel Implementation:**
```php
Route::get('dioceses/{diocese}/parishes', [ParishController::class, 'byDiocese']);
Route::get('dioceses/{diocese}/contacts', [ContactController::class, 'byDiocese']);
```

---

## 5. Implementation Recommendations

### Phase 1: Laravel Setup with Authentication
1. Install Laravel 11.5.0 (latest stable)
2. Install Laravel Breeze v2.3.0+ (Blade stack)
3. Configure database (PostgreSQL recommended)
4. Set up basic authentication flows
5. Test login/registration

### Phase 2: Sanctum API Authentication
1. Install and configure Sanctum v4.0.7
2. Create API authentication endpoints
3. Protect API routes with `auth:sanctum` middleware
4. Implement token abilities for role-based access
5. Test with Postman/Insomnia

### Phase 3: Data Models Migration
1. Create migrations matching Node.js schema
2. Define Eloquent models with relationships
3. Implement API Resource classes
4. Create seeder data for testing
5. Ensure edge cases are handled

### Phase 4: API Controllers
1. Implement RESTful controllers for each resource
2. Maintain existing API contract
3. Preserve query parameter filtering
4. Implement nested resource routes
5. Add comprehensive validation

### Phase 5: Caddy Deployment
1. **For Production:** Acquire domain name
2. Set up Laravel environment on server
3. Configure PHP-FPM
4. Create Caddyfile with Laravel configuration
5. Test HTTPS and certificate auto-renewal
6. Configure logging and monitoring

### Technology Stack Recommendation

```
Frontend:    Laravel Blade + Tailwind CSS (via Breeze v2.3.0+)
Backend:     Laravel 11.5.0 + Sanctum v4.0.7
Database:    PostgreSQL
Web Server:  Caddy 2.x
PHP:         8.2, 8.3, or 8.4 (8.3+ recommended)
Cache:       Redis (optional, for sessions/queues)
```

**Note on PHP Version:** Laravel 11.5.0 supports PHP 8.2, 8.3, and 8.4. PHP 8.3 or 8.4 is recommended for better performance and latest language features.

### Database Schema Considerations

1. **Use UUID or ULID for IDs** - Better for distributed systems, matches Node.js GUID approach
2. **Soft Deletes** - Preserve historical data
3. **Timestamps** - Always include `created_at`, `updated_at`
4. **Nullable Foreign Keys** - Support flexible hierarchies
5. **Indexes** - Add on frequently queried fields (dioceseId, state, type, etc.)
6. **JSON Columns** - Use for flexible data like addresses, mass schedules
7. **Enums** - Laravel 11 native enum support for types/roles

---

## 6. Risk Assessment & Mitigation

### Risk 1: API Contract Breaking Changes
**Mitigation:** 
- Implement thorough API integration tests
- Use Laravel API Resources to match Node.js response format
- Version API endpoints (`/api/v1/`)

### Risk 2: Caddy IP-Only HTTPS Limitations
**Mitigation:**
- **Strong recommendation:** Acquire domain name for production
- Use nip.io for testing if domain not available
- Document self-signed certificate installation for internal use

### Risk 3: Data Model Complexity
**Mitigation:**
- Incremental migration, start with core entities (Diocese, Parish, Contact)
- Comprehensive relationship testing
- Use Laravel's eager loading to prevent N+1 queries

### Risk 4: Authentication Token Management
**Mitigation:**
- Implement token expiration
- Support token refresh mechanism
- Add token revocation on logout
- Monitor for suspicious token activity

---

## 7. Next Steps

1. ✅ **Research Complete** - This document
2. ⏭️ Create Laravel project structure
3. ⏭️ Implement Breeze authentication
4. ⏭️ Configure Sanctum API authentication
5. ⏭️ Design database schema
6. ⏭️ Create migrations and models
7. ⏭️ Implement API controllers
8. ⏭️ Set up Caddy configuration
9. ⏭️ Deploy to staging environment
10. ⏭️ Test and validate

---

## References

### Laravel Framework
- [Laravel 11.x Release Notes](https://laravel.com/docs/11.x/releases) - Official release notes
- [Laravel 11.5.0 Features](https://laravel-news.com/laravel-11-5-0) - Latest version updates

### Laravel Authentication
- [Laravel 11 Authentication Documentation](https://laravel.com/docs/11.x/authentication)
- [Laravel Breeze Documentation](https://laravel.com/docs/11.x/starter-kits#laravel-breeze)
- [Laravel Breeze GitHub Releases](https://github.com/laravel/breeze/releases) - Latest version info
- [Laravel UI Documentation](https://github.com/laravel/ui)

### Laravel Sanctum
- [Laravel Sanctum Documentation](https://laravel.com/docs/11.x/sanctum)
- [Sanctum API Authentication Guide](https://laravel.com/docs/11.x/sanctum#api-token-authentication)
- [Sanctum GitHub Releases](https://github.com/laravel/sanctum/releases) - Latest version v4.0.7

### Caddy
- [Caddy Automatic HTTPS](https://caddyserver.com/docs/automatic-https)
- [Caddy Laravel Deployment](https://caddyserver.com/docs/caddyfile/patterns#php)

### API Design
- [RESTful API Best Practices](https://github.com/microsoft/api-guidelines)
- [Laravel API Resources](https://laravel.com/docs/11.x/eloquent-resources)

---

**Document Version:** 2.0  
**Last Updated:** 2024-12-27 (Updated with Laravel 11.5.0, Sanctum v4.0.7, Breeze v2.3.0+)  
**Author:** Research Agent  
**Status:** Planning/Research Complete - Updated with Latest Packages
