# Agent Handoff (Dec 23, 2025)

This document summarizes the repository’s current state, what is implemented, and the most likely next steps.

## Repo Structure (high level)

This repo contains two codebases:

1) **Laravel app** (primary for the SPA + MCP work)
- Location: `laravel/`
- Stack: Laravel 12, Jetstream + Inertia (Vue 3) + Teams (multi-tenant)
- Tests: PHPUnit via `php artisan test`

2) **TypeScript package**
- Location: `src/`
- Appears to be a Node/TS API layer with Jest tests.
- Not the focus of the recent work described below.

## What’s Implemented (Laravel)

### 1) SPA foundation (Jetstream + Inertia + Teams)
- Jetstream Teams are enabled (multi-tenant scaffolding).
- Vue 3 Inertia pages/components exist under `laravel/resources/js/`.

### 2) MCP (Model Context Protocol) integration
- MCP routes are registered in `laravel/routes/ai.php`.
- The MCP server class is `App\Mcp\Servers\PublicServer`.
- Tools registered on the public server:
  - `ping`
  - Parish tools: `search-parishes`, `get-parish`, `create-parish`, `update-parish`
  - Google Places tools: `search-google-places`, `get-google-place`, `create-parish-from-google-place`

Note: Laravel 12 did not auto-load `routes/ai.php`; route loading is handled in bootstrap.

### 3) Parishes: read/write + Google Place identity
- Parishes now include Google identity/location fields (place id, formatted address, maps URL, lat/lng).
- Creating a parish from a Google place_id is **idempotent** (returns existing record if already created).
- Safeguard exists to prevent a place_id being linked to a parish under a different diocese.

### 4) Google Places API (for SPA use)
Two JSON endpoints exist:
- `GET /api/v1/google-places/search`
  - Supports optional geo bias params: `lat`, `lng`, `radius_meters`
- `GET /api/v1/google-places/details`

Configuration:
- `laravel/config/services.php` expects `google_places.api_key` (usually via `.env`).

### 5) Diocese search endpoint (for cross-diocese selection)
- `GET /api/v1/dioceses/search`
  - Query params:
    - `query` (string)
    - `limit` (int)
    - `include_external` (bool)
  - Returns `{ query, count, results: [...] }`

Tests:
- `laravel/tests/Feature/Api/V1/DioceseSearchTest.php`

## Multi-Tenancy Direction (Current Design)

### Tenant definition
- **Diocese is the tenant**.
- Implementation uses Jetstream Teams.

### Cross-diocese contacts requirement
- Contacts must be *owned/scoped* by the tenant diocese, but may *reference* other dioceses.

To support this, the schema/design now separates:
- `contacts.owner_diocese_id` (tenant ownership boundary; should be enforced)
- `contacts.diocese_id` (the referenced diocese; can be external)

### Team ↔ diocese mapping
- Teams can be mapped to a diocese via `teams.jurisdiction_id`.

These are scaffolded with migrations + model relationships:
- `Team::jurisdiction()`
- `Contact::ownerDiocese()`

Important: enforcement (policies / middleware / query scoping) is not fully implemented yet.

## How to Run (Laravel)

From `laravel/`:

- Install PHP deps: `composer install`
- Install JS deps: `npm install`
- Copy env: `cp .env.example .env` then set app key: `php artisan key:generate`
- Run migrations: `php artisan migrate`
- Run tests: `php artisan test`
- Dev servers:
  - API/app: `php artisan serve`
  - Vite: `npm run dev`

## Key Files to Know

- MCP routes: `laravel/routes/ai.php`
- MCP server registry: `laravel/app/Mcp/Servers/PublicServer.php`
- MCP tools:
  - `laravel/app/Mcp/Tools/*Parish*`
  - `laravel/app/Mcp/Tools/*GooglePlace*`
- Google Places controller/client:
  - `laravel/app/Http/Controllers/Api/V1/GooglePlacesController.php`
  - `laravel/app/Services/GooglePlaces/GooglePlacesClient.php`
- API routes: `laravel/routes/api.php`
- Diocese search controller: `laravel/app/Http/Controllers/Api/V1/DioceseController.php`

## Known Gaps / Things to Watch

- The top-level README describes many REST endpoints that are not implemented in the Laravel app yet.
- Tenant scoping is not enforced yet for parish/contact mutations.
- Contact create/update endpoints and MCP tools for contacts are not implemented yet.

## Suggested Next Steps (most likely)

1) **Finalize tenant scoping rules**
- Decide the canonical rule for tenant diocese resolution (likely: current team’s `jurisdiction_id`).
- Add middleware/policies to enforce that parish writes happen only within the tenant diocese.

2) **Implement contact APIs that respect ownership vs reference**
- On create/update, always set `owner_diocese_id` from the current tenant.
- Allow `diocese_id` (referenced diocese) to point to any jurisdiction (including external).
- Add tests verifying cross-diocese references are allowed but cross-tenant ownership access is not.

3) **Expose selection UX endpoints as needed**
- Diocese search exists; add parish search (scoped / unscoped depending on workflow) as needed.

4) **Optional: Add MCP tools for contact workflows**
- `search-dioceses` (wrap `/api/v1/dioceses/search` behavior)
- `create-contact`, `update-contact` enforcing `owner_diocese_id` from tenant context.

5) **Developer ergonomics**
- If desired, add (or regenerate) a VS Code MCP config for the Laravel MCP server command (Laravel Boost).
