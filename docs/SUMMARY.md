# Research Summary: Laravel MVP (Repo Reality) + Auth + Deployment

**As-of:** December 26, 2025 — 7:27 PM (America/Chicago)  
**Task:** Update prior Laravel MVP research against (1) current official Laravel guidance and (2) the actual repository state on GitHub (`origin/main`).  
**Status:** ✅ Updated

## What Changed Since the Original Research

The repository is no longer in a “plan a new Laravel app” state.

On `origin/main`, a Laravel application already exists under `laravel/` and is configured with:

- **Laravel Framework:** `^12.0`
- **Authentication UI:** **Jetstream** (`^5.4`) using the **Inertia** stack
- **Inertia adapter:** `inertiajs/inertia-laravel` (`^2.0`)
- **Frontend:** Vue 3 + Inertia (`@inertiajs/vue3`), Vite, Tailwind
- **API/Auth:** Sanctum (`^4.0`) is installed and the `User` model already uses `HasApiTokens`
- **AI / tooling:** `laravel/mcp` (`^0.5.1`) is installed and a public MCP endpoint exists

This means the old “Laravel 11 + Breeze (Blade)” recommendation is obsolete for this repo: the scaffold choice has already been made.

## Key Findings (Updated)

### 1) Authentication: Keep Jetstream + Inertia (Already Implemented)

**Recommendation:** Treat Jetstream + Inertia as the baseline and build on it.

**Why:**
- It is already installed and wired into the app.
- It provides a solid default auth surface area (session auth, teams, etc.) without re-scaffolding.
- It aligns with current official guidance emphasizing first-party starter kits and modern stacks.

### 2) API Authentication: Use Sanctum for Token Auth (Already Installed)

**Recommendation:** Use Sanctum for API token authentication on `/api/v1/*`.

**Notes grounded in Laravel 12 docs:**
- Laravel’s docs describe `install:api` as the standard way to install API auth primitives.
- Sanctum supports both cookie-based SPA auth and token-based API auth; we should use **token-based auth for third-party / programmatic clients** and keep Jetstream’s normal auth for the UI.

### 3) Deployment: Domain Still Required for Public Trusted HTTPS

**Still true:** Public CAs won’t issue trusted certs for bare IPs.

- If you deploy behind **Caddy** or **Nginx**, you still need a domain for public trusted TLS.
- Official Laravel deployment guidance currently calls out **Nginx** and also mentions **FrankenPHP** as a modern option; either way, the “don’t serve from project root” rule and `public/` web root remains the same.

### 4) Node.js API Contract: Edge Cases Still Matter

The Node/TypeScript codebase expresses domain nuances we still need to preserve. The Laravel app on `origin/main` already reflects some of that direction (e.g., contacts + jurisdictions + UUID string keys), but the overall API surface is not yet at parity.

## Current Repo Implementation Snapshot (origin/main)

### Database / Models (Exists)

- `Jurisdiction`, `Parish`, `Contact`, `User` (string UUID primary keys for domain models)
- Migrations exist for contacts / jurisdictions / parishes, personal access tokens, and Jetstream teams.

### API (Partial)

- `routes/api.php` currently exposes a small subset under `/api/v1/*`:
  - `GET /api/v1/dioceses/search`
  - Google Places search/details endpoints

Other `Api\V1` controllers exist but are mostly placeholders, indicating the API migration is in progress.

### MCP Endpoint (Exists)

- A public MCP server is registered and exposed at `/mcp/public`.

## Revised Implementation Roadmap

### Phase 0: Align Workspaces With Reality
- Ensure your local working tree matches the tracked Laravel app in `origin/main` (avoid vendor/cache/runtime artifacts in git).
- Run the Laravel app using its own scripts (`composer run dev`, etc.) and confirm auth screens load.

### Phase 1: Decide the Auth Contract for API Clients
- Confirm whether API clients will authenticate via:
  - **Bearer tokens** (Sanctum personal access tokens), or
  - **Session cookies** (first-party SPA).
- Implement the minimal endpoints needed for your clients (issue/revoke tokens) under `/api/v1`.

### Phase 2: Expand `/api/v1` Toward Node Contract Parity
- Implement the core resources and filtering/nested routes already described in the root Node/TypeScript API contract.
- Add request validation and consistent JSON response shapes.

### Phase 3: Testing + Backwards Compatibility
- Add Laravel feature tests around the API contract and edge cases.
- Keep `/api/v1/*` stable and versioned.

### Phase 4: Deployment
- Use a domain for trusted TLS.
- Follow the official deployment guidance (web root is `public/`, run `php artisan optimize` during deploy).
- Choose your web server (Caddy/Nginx/FrankenPHP) based on ops constraints.

**Status:** Ready to proceed to implementation phase pending approval and domain acquisition.
