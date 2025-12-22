# Research Summary: Laravel MVP with Auth + Caddy

**Date:** December 22, 2024  
**Task:** Planning-only research for Laravel MVP implementation  
**Status:** ✅ Complete

## Overview

This research phase has investigated the best approaches for:
1. Laravel authentication scaffolding with login forms
2. Laravel Sanctum API token authentication
3. Caddy web server deployment with HTTPS constraints (IP-only vs domain)
4. Preservation of existing Node.js API contract and edge cases

## Key Findings

### 1. Laravel Authentication: Use Laravel Breeze

**Recommendation:** Laravel Breeze (Blade stack)

**Rationale:**
- Minimal, clean, easy to understand
- Perfect for MVP development
- Includes login, registration, password reset
- Tailwind CSS (customizable)
- Well-documented and maintained

**Alternative considered:**
- Laravel UI (older, Bootstrap-based)
- Laravel Jetstream (too feature-heavy for MVP)

### 2. Laravel Sanctum: Ideal for API Authentication

**Recommendation:** Laravel Sanctum for API token management

**Key Benefits:**
- Simple token-based authentication
- Perfect for SPAs and mobile apps
- Lighter than OAuth (Passport)
- Built-in token abilities/scopes
- Easy integration with Breeze

**Setup Process:**
1. Install: `php artisan install:api`
2. Add `HasApiTokens` trait to User model
3. Configure `auth:sanctum` middleware
4. Implement login/logout endpoints
5. Issue tokens on successful authentication

### 3. Caddy HTTPS Deployment: Domain Required for Production

**Critical Finding:** Caddy cannot obtain trusted public certificates for bare IP addresses.

**Options Evaluated:**

| Option | Use Case | Certificate Type | Browser Trust |
|--------|----------|------------------|---------------|
| IP-only (self-signed) | Development/Internal | Self-signed | ❌ Warnings |
| IP with internal CA | Enterprise internal | Custom CA | ⚠️ If CA distributed |
| nip.io workaround | Testing | Let's Encrypt | ✅ Trusted |
| **Real domain** | **Production** | **Let's Encrypt** | **✅ Trusted** |

**Strong Recommendation:** Acquire a domain name for production deployment.

**Workaround for testing:** Use services like nip.io (e.g., `192.168.1.100.nip.io`) to get real certificates without buying a domain.

### 4. Node.js API Contract Edge Cases

**Critical Edge Cases Identified:**

1. **External Diocese Tracking**
   - Contacts can belong to diocese A but be from diocese B
   - Requires: `dioceseId`, `homeDioceseId`, `isExternalContact` fields

2. **Multiple Positions per Contact**
   - One contact can hold multiple simultaneous roles
   - Requires: Separate `ContactPosition` model with one-to-many relationship

3. **Organization Scope Hierarchy**
   - Organizations span parish → diocesan → national → international
   - Requires: `scope` enum field, nullable `dioceseId` for national/international

4. **SVDP Multi-Level Structure**
   - St. Vincent de Paul has 5-level hierarchy
   - Requires: Self-referential `parentOrganizationId`, `svdpLevel` field

5. **Sacred Site Designations**
   - Parishes can be cathedrals, basilicas, shrines
   - Requires: Boolean flags + type enums

6. **Flexible Parish Hierarchy**
   - Parishes may/may not belong to regions/deaneries
   - Requires: Nullable foreign keys with proper cascade handling

7. **Polymorphic Contact Associations**
   - Contacts link to multiple entity types
   - Options: Nullable FKs (simple) vs polymorphic relationships (flexible)

8. **Address Reusability**
   - Common address structure across entities
   - Recommended: JSON column with array casting

**API Response Format:**
```json
{
  "success": true,
  "data": {...},
  "count": 10
}
```

**Must preserve:**
- `/api/v1/*` endpoint structure
- Query parameters: `dioceseId`, `state`, `type`, `role`, `scope`
- Nested routes: `/dioceses/:id/parishes`, `/parishes/:id/contacts`
- All 12 core resource types

## Implementation Roadmap

### Phase 1: Foundation
- Install Laravel 11
- Set up Laravel Breeze
- Configure PostgreSQL database
- Test authentication flows

### Phase 2: API Authentication
- Install Sanctum
- Create auth endpoints
- Implement token management
- Add role-based token abilities

### Phase 3: Data Models
- Create migrations (preserving edge cases)
- Define Eloquent models
- Implement relationships
- Create API resources

### Phase 4: API Controllers
- Implement RESTful controllers
- Maintain API contract compatibility
- Add query parameter filtering
- Create nested resource routes

### Phase 5: Deployment
- **Acquire domain name** (critical)
- Set up server environment
- Configure Caddy
- Deploy and test HTTPS
- Configure monitoring

## Technology Stack

```
Frontend:    Laravel Blade + Tailwind CSS (via Breeze)
Backend:     Laravel 11 + Sanctum
Database:    PostgreSQL (recommended for production)
Web Server:  Caddy 2.x
PHP:         8.2+
Optional:    Redis (caching, queues)
```

## Risk Mitigation

1. **API Breaking Changes:** Version endpoints (`/api/v1/`), comprehensive tests
2. **HTTPS Constraints:** Acquire domain; use nip.io for testing
3. **Data Complexity:** Incremental migration, start with core entities
4. **Token Security:** Implement expiration, refresh, revocation

## Documentation Created

1. **[LARAVEL_MVP_RESEARCH.md](./LARAVEL_MVP_RESEARCH.md)** - Complete research findings (19KB)
2. **[QUICK_REFERENCE.md](./QUICK_REFERENCE.md)** - Developer quick reference (7KB)
3. **[SUMMARY.md](./SUMMARY.md)** - This file

## Next Steps

The research phase is complete. Next steps for implementation:

1. ⏭️ Obtain approval to proceed with Laravel migration
2. ⏭️ **Acquire domain name** for production deployment
3. ⏭️ Set up Laravel project with Breeze
4. ⏭️ Implement Sanctum authentication
5. ⏭️ Design and create database schema
6. ⏭️ Migrate API endpoints
7. ⏭️ Configure Caddy with production domain
8. ⏭️ Deploy to staging
9. ⏭️ Test and validate
10. ⏭️ Production deployment

## Recommendations

### Critical
- ✅ Use Laravel Breeze for authentication scaffolding
- ✅ Use Laravel Sanctum for API tokens
- ⚠️ **Acquire a domain name for production** (non-negotiable for trusted HTTPS)

### Strongly Recommended
- Use PostgreSQL for production database
- Implement UUID/ULID for primary keys
- Add soft deletes for data preservation
- Use Laravel API Resources for consistent responses
- Implement comprehensive testing
- Use Redis for caching and queues

### For Consideration
- Start with Blade templates, consider Inertia.js later for SPA features
- Implement role-based access control using Sanctum token abilities
- Add rate limiting on authentication endpoints
- Configure automated backups
- Set up logging and monitoring from day one

## Conclusion

The research confirms that Laravel 11 with Breeze and Sanctum provides an excellent foundation for the DRM Catholic MVP. The main deployment constraint is the requirement for a domain name to enable trusted HTTPS with Caddy. All existing Node.js API edge cases can be preserved in the Laravel implementation with careful database schema design and Eloquent relationship configuration.

**Status:** Ready to proceed to implementation phase pending approval and domain acquisition.
