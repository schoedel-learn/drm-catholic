# Implementation Checklist (Repo Reality)

This checklist is updated to match:
- current official Laravel guidance (Laravel 12.x era), and
- the actual repository state on GitHub (`origin/main`).

**As-of:** December 26, 2025 — 7:27 PM (America/Chicago)

## Baseline Reality (origin/main)

- A Laravel app already exists under `laravel/`.
- Laravel `^12.0` with Jetstream `^5.4` (Inertia stack) and Vue 3.
- Sanctum `^4.0` is installed and `HasApiTokens` is already on `App\\Models\\User`.
- Domain models and migrations already exist for: jurisdictions, parishes, contacts.
- `/api/v1` exists but is partial (e.g., `GET /api/v1/dioceses/search`).

## Phase 0: Workspace Hygiene (Do This First)

- [ ] Confirm you are working from tracked files (avoid committing `vendor/`, cache, runtime artifacts).
- [ ] Ensure the checked-out `laravel/` directory matches what’s in GitHub.

## Phase 1: Local Dev Bring-Up (Est. 0.5–1 day)

- [ ] `cd laravel`
- [ ] Install deps: `composer install` and `npm install`
- [ ] Create `.env` (copy from `.env.example` if missing)
- [ ] Generate key: `php artisan key:generate`
- [ ] Migrate DB: `php artisan migrate`
- [ ] Run dev services: `composer run dev`
- [ ] Verify health route: `GET /up`
- [ ] Verify UI auth works (Jetstream): login and dashboard

## Phase 2: Decide the API Auth Contract (Est. 0.5 day)

- [ ] Confirm API clients:
  - [ ] First-party web UI only
  - [ ] Programmatic/third-party clients
  - [ ] Mobile apps
- [ ] Decide which auth mode(s) to support:
  - [ ] Session cookies (first-party UI)
  - [ ] Bearer tokens (Sanctum personal access tokens)

## Phase 3: Implement `/api/v1` Token Endpoints (Est. 1 day)

If you need programmatic access, add explicit token endpoints under `/api/v1`.

- [ ] Add routes under `routes/api.php` within the `Route::prefix('v1')` group
- [ ] Implement token issuance using `$user->createToken(...)`
- [ ] Implement token revocation (current token, all tokens, or by id)
- [ ] Protect token endpoints with `auth:sanctum`
- [ ] Ensure consistent JSON responses (and stable error shapes)

## Phase 4: API Parity Against Node/TypeScript Contract (Iterative)

Implement endpoints as-needed, matching the contract already expressed in the Node/TypeScript API.

- [ ] Add/expand controllers under `App\\Http\\Controllers\\Api\\V1`
- [ ] Preserve filtering and nested-route behavior where required
- [ ] Keep versioning stable (`/api/v1/*`)

## Phase 5: Data Model Completion (Iterative)

- [ ] Review existing migrations for jurisdictions/parishes/contacts
- [ ] Add missing tables only when needed by the next endpoint(s)
- [ ] Add indexes for common filters (e.g., diocese_id, state, type)
- [ ] Add seeders for local dev (optional but recommended)

## Phase 6: Testing (Iterative)

- [ ] Add Laravel feature tests for `/api/v1` endpoints
- [ ] Add tests for edge cases carried over from the Node contract

## Phase 7: Deployment (Est. 1–2 days)

- [ ] **Acquire a domain name** if you need trusted public HTTPS
- [ ] Choose server approach (Nginx / Caddy / FrankenPHP)
- [ ] Serve only from `public/` (never the project root)
- [ ] Run `php artisan optimize` during deploy
- [ ] Ensure `APP_DEBUG=false` in production

---

## Notes

- The old “create a new Laravel 11 + Breeze app” plan is obsolete for this repo.
- Jetstream + Inertia is already installed; treat it as baseline and avoid re-scaffolding.

- [ ] Block direct access to port 8000 (if using)

## Phase 11: Monitoring & Optimization (Est. 1 day)

### Logging
- [ ] Configure Laravel logging
- [ ] Configure Caddy access/error logs
- [ ] Set up log rotation

### Monitoring
- [ ] Set up application monitoring
- [ ] Monitor database performance
- [ ] Set up alerts for errors

### Optimization
- [ ] Enable OPcache
- [ ] Configure Redis for caching (optional)
- [ ] Configure Redis for queues (optional)
- [ ] Optimize database queries
- [ ] Set up CDN for assets (optional)

### Security
- [ ] Review security headers
- [ ] Implement rate limiting
- [ ] Configure session security
- [ ] Review CORS settings
- [ ] Set up automated backups
- [ ] Configure SSL/TLS properly

## Phase 12: Documentation (Est. 1 day)

### API Documentation
- [ ] Document all endpoints
- [ ] Document request/response formats
- [ ] Document authentication process
- [ ] Provide example requests
- [ ] Document error codes

### Deployment Documentation
- [ ] Document deployment process
- [ ] Document server requirements
- [ ] Document environment variables
- [ ] Create runbook for common issues

### Developer Documentation
- [ ] Document code structure
- [ ] Document database schema
- [ ] Document testing procedures
- [ ] Create contributing guide

## Post-Implementation

### Code Review
- [ ] Review all code
- [ ] Check for security issues
- [ ] Verify error handling
- [ ] Check code quality

### Final Testing
- [ ] Run full test suite
- [ ] Test on staging environment
- [ ] Perform load testing
- [ ] Test edge cases
- [ ] Test failure scenarios

### Go-Live Checklist
- [ ] Final database backup
- [ ] Deploy to production
- [ ] Test production deployment
- [ ] Monitor for issues
- [ ] Document any issues found

## Maintenance

### Regular Tasks
- [ ] Monitor application logs and error rates
- [ ] Keep dependencies updated (Composer + npm)
- [ ] Review DB indexes / slow queries as data grows
- [ ] Apply security patches regularly

---

## Estimated Timeline (High-Level)

- Phase 0 (Hygiene): 0.5 day
- Phase 1 (Bring-up): 0.5–1 day
- Phase 2 (Auth contract decision): 0.5 day
- Phase 3 (Token endpoints): ~1 day (if needed)
- Phase 4–6 (API parity + data model + tests): iterative (days → weeks, depending on scope)
- Phase 7 (Deployment): 1–2 days (plus domain acquisition lead time)

## Resources

- Laravel Documentation: https://laravel.com/docs
- Laravel Starter Kits: https://laravel.com/docs/12.x/starter-kits
- Laravel Sanctum: https://laravel.com/docs/12.x/sanctum
- Laravel Deployment: https://laravel.com/docs/12.x/deployment
- Laravel Jetstream: https://jetstream.laravel.com/
- Caddy Documentation: https://caddyserver.com/docs

---

**Note:** This checklist is intentionally iterative; treat the Node/TypeScript API contract as the source-of-truth for what endpoints to build next.
