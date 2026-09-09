# DRM Catholic Repository Rehabilitation Design

**Status:** Approved for implementation planning  
**Repository:** `schoedel-learn/drm-catholic`  
**Canonical local checkout:** `/home/barry-schoedel/Documents/Development/drm-catholic`

## Objective

Restore DRM Catholic to a clean, maintainable development posture:

- The workstation is the primary development environment.
- GitHub pull requests are the review and quality gate.
- Merges to `main` automatically deploy a disposable public demo to Google Cloud Run.
- The Laravel application is the canonical product implementation.
- Stale branches, duplicate application surfaces, and dependency noise are removed without losing unique behavior.

## Current State

- Local `main` matches `origin/main` at `be88ed1`.
- Six uncommitted files contain useful local-preview and Inertia authentication fixes. They must be preserved and reviewed, not discarded.
- GitHub has 34 open pull requests, mostly stale or superseded Dependabot updates.
- CI tests both the root TypeScript/Express prototype and the Laravel application.
- CodeQL scans JavaScript only.
- No confirmed demo deployment exists.
- The root Express API duplicates much of the Laravel domain model and has no known deployed consumer.

## Chosen Approach

Use a Laravel-first consolidation:

1. Preserve the local changes on `chore/repository-rehabilitation`.
2. Prove whether the root Express package contains unique behavior or active consumers.
3. Retire the root package when its useful behavior is represented in Laravel.
4. Modernize the remaining Laravel dependencies in small, reviewable groups.
5. Replace the existing pull-request backlog with a controlled update sequence.
6. Protect `main` with required pull-request checks.
7. Deploy each successful merge to a resettable Cloud Run demo.

This is preferred over maintaining both applications because duplicate domain models and dependency streams create unnecessary drift. It is preferred over a big-bang upgrade because smaller pull requests preserve causal clarity when a build or test fails.

## Repository Structure

The Laravel application under `laravel/` becomes the sole runtime product.

Before removing the root TypeScript package:

- Inventory its routes, models, validation, and tests.
- Map each unique capability to an existing Laravel equivalent.
- Port any still-required behavior with tests.
- Confirm no workflow, deployment, documentation, or external integration consumes the Express API.

After that proof, remove the root package, its lockfile, TypeScript configuration, ESLint configuration, Jest configuration, and root-only CI jobs. Repository-level documentation and community files remain at the root.

## Local Development

Local development uses the workstation-installed PHP, Composer, Node.js, and npm toolchains. The supported runtime versions are documented and enforced consistently in Composer, npm, and CI.

The standard setup flow will:

- Copy `.env.example` only when `.env` does not exist.
- Create a local SQLite database.
- Generate an application key.
- Run migrations and safe demo seeders.
- Install locked PHP and JavaScript dependencies.
- Build frontend assets.

The existing local changes are the starting point for this flow, but the setup script must not overwrite an intentional developer `.env`.

Local preview data may use a documented demonstration account. The public demo must use a separate demo seeder and must not expose a reusable production-style password or permit uncontrolled registration.

## GitHub Review Flow

Development uses short-lived `feature/`, `fix/`, and `chore/` branches. Changes reach `main` only through pull requests.

Branch protection for `main` will require:

- At least one approving review.
- Passing Laravel tests.
- Passing frontend build and lint checks.
- Passing dependency and security checks.
- Conversation resolution.
- A branch current with `main` before merge.

Direct pushes and force pushes to `main` are disabled. Squash merge is the default so each pull request produces one coherent commit.

The existing pull requests will be triaged as follows:

- Close stale version-by-version Dependabot pull requests as superseded.
- Close obsolete Copilot and branch-protection pull requests after preserving any still-useful intent.
- Recreate dependency updates from the rehabilitated baseline in compatibility groups.
- Merge only updates that pass the full review pipeline.

Dependabot will use grouped updates, conservative open-pull-request limits, and separate groups for Composer, Laravel frontend npm, and GitHub Actions. Major upgrades remain individually reviewed.

## Continuous Integration

Pull-request CI will operate only on the canonical Laravel product and will use locked installs:

- Composer validation and dependency installation.
- Laravel Pint in check mode.
- Frontend dependency installation with `npm ci`.
- Frontend linting and production build.
- Laravel unit and feature tests.
- Database migrations against the CI database.
- CodeQL for the languages actually present after consolidation.

The same build inputs used by CI must produce the Cloud Run artifact. Deployment cannot substitute an untested dependency resolution or source tree.

## Cloud Run Demo

GitHub Actions deploys a containerized Laravel application to a dedicated Cloud Run demo service after a successful merge to `main`.

Authentication uses Google Cloud Workload Identity Federation. This is keyless authentication: GitHub exchanges its short-lived identity for narrowly scoped Google Cloud permissions instead of storing a long-lived service-account key.

The GitHub `demo` environment records the public URL and contains deployment variables. Sensitive runtime values are stored in Google Secret Manager and exposed only to the Cloud Run service.

The demo is intentionally disposable:

- It contains generated sample data only.
- It runs a single Cloud Run instance to avoid multiple independent SQLite copies.
- Its database is created, migrated, and seeded when a revision starts.
- Data may reset after a deployment, restart, or scale event.
- Registration and password-reset email delivery are disabled.
- Debug mode is disabled.
- Search indexing is blocked with `X-Robots-Tag: noindex, nofollow`.
- No production, parish, clergy, donor, or contact data is used.

This architecture minimizes cost and data-handling risk. If persistent stakeholder testing becomes necessary, the demo will migrate to Cloud SQL through a separately reviewed infrastructure change.

## Deployment Safety

The deployment workflow has these gates:

1. Pull-request CI validates the exact source revision.
2. Merge to `main` builds an immutable container tagged with the Git commit SHA.
3. GitHub authenticates through Workload Identity Federation.
4. Cloud Run creates a new revision.
5. A post-deploy smoke test checks the health endpoint, public landing page, authentication page, deployed SHA, and no-index header.
6. A failed smoke test fails the workflow and leaves the prior healthy revision available for rollback.

Deployment concurrency cancels obsolete in-progress runs so an older commit cannot replace a newer demo revision.

## Error Handling and Observability

The application emits structured logs to standard output and standard error for Cloud Logging. Health checks distinguish application readiness from successful data initialization.

Startup fails loudly when migrations, seeding, or required configuration fail. The workflow must not report success through a fallback page or a swallowed exception.

The deployed revision exposes a non-sensitive build identifier so the workflow can prove that the public demo matches the merged commit.

## Implementation Sequence

1. Preserve and validate the six existing local changes.
2. Establish a green local baseline for both current packages.
3. Audit and retire or port the root Express package.
4. Consolidate scripts, documentation, runtime versions, and CI around Laravel.
5. Update dependencies in compatible groups with tests after each group.
6. Reconfigure Dependabot and close superseded pull requests.
7. Add the production container and local container build validation.
8. Provision the dedicated Google Cloud demo identity and Cloud Run service.
9. Add the GitHub `demo` environment and deployment workflow.
10. Configure `main` branch protection after required checks exist.
11. Deploy and verify the first resettable demo revision.
12. Remove stale remote branches only after their useful work is accounted for.

## Acceptance Criteria

- A clean clone can be set up locally from documented commands.
- Local setup does not overwrite an existing `.env`.
- The repository has one canonical runtime application.
- Supported runtime and dependency versions are explicit and consistent.
- All required pull-request checks pass from a clean checkout.
- `main` cannot be changed without pull-request review.
- Dependabot produces a small, actionable update queue.
- A merge to `main` deploys the same tested revision to Cloud Run.
- The public demo contains only resettable sample data and blocks indexing.
- The deployed commit is visible through a non-sensitive health response.
- Rollback to the previous healthy Cloud Run revision is documented and verified.
