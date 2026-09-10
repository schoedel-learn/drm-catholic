# DRM Catholic Repository Rehabilitation Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use `executing-plans` to implement this plan task by task. Use `test-driven-development` for behavior changes and `verification-before-completion` before every commit.

**Goal:** Rehabilitate DRM Catholic into one current, maintainable Laravel application developed on the workstation, reviewed through GitHub pull requests, and deployed from merged `main` to a disposable Google Cloud Run demo.

**Architecture:** Keep `laravel/` as the sole product runtime, retire the root in-memory Express prototype after a consumer/parity audit, and standardize local development on PHP 8.4, Node 24, SQLite, Composer, and npm. Package the Laravel application with FrankenPHP, initialize disposable SQLite data at container startup, and deploy immutable commit images from GitHub Actions through Workload Identity Federation.

**Technology stack:** Laravel 13, PHP 8.4, Vue 3, Inertia 2, current stable Vite and Tailwind CSS, Node 24, npm, SQLite, PHPUnit, Laravel Pint, ESLint flat config, Prettier, Docker, FrankenPHP 1, GitHub Actions, Artifact Registry, Secret Manager, Workload Identity Federation, Google Cloud Run.

**Fixed deployment identifiers:**

| Resource | Value |
|---|---|
| GitHub repository | `schoedel-learn/drm-catholic` |
| Google Cloud project ID | `drm-catholic-demo-1117974782` |
| Region | `us-central1` |
| Artifact Registry repository | `drm-demo` |
| Cloud Run service | `drm-catholic-demo` |
| Runtime service account | `drm-demo-runtime@drm-catholic-demo-1117974782.iam.gserviceaccount.com` |
| Deploy service account | `github-drm-demo@drm-catholic-demo-1117974782.iam.gserviceaccount.com` |
| Secret Manager secret | `drm-demo-app-key` |
| Demo password secret | `drm-demo-user-password` |
| GitHub environment | `demo` |

---

## Task 1: Freeze the rehabilitation baseline

**Files:**
- Modify: none
- Verify: `README.md`
- Verify: `laravel/.env.example`
- Verify: `laravel/app/Providers/FortifyServiceProvider.php`
- Verify: `laravel/composer.json`
- Verify: `laravel/config/database.php`
- Verify: `laravel/routes/web.php`

- [ ] Run `git status --short --branch`, `git worktree list`, `git diff --check`, and `git diff --stat` from the repository root.
- [ ] Save the six-file patch outside the repository with `git diff -- README.md laravel/.env.example laravel/app/Providers/FortifyServiceProvider.php laravel/composer.json laravel/config/database.php laravel/routes/web.php > /home/barry-schoedel/.copilot/session-state/61f16969-a018-4553-8242-c34d2a48c75b/files/drm-local-preview.patch`.
- [ ] Record hashes with `sha256sum` for the patch and the six working-tree files in the session artifact directory.
- [ ] Run the current root and Laravel baselines without modifying dependencies: `npm test -- --runInBand`; then `cd laravel && DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test` so the workstation baseline does not require an undocumented PostgreSQL service.
- [ ] Preserve failures as baseline evidence, then resolve failures required to make both locked baselines green before changing architecture; pause for approval if a truly unrelated failure requires scope expansion.
- [ ] Do not proceed to Express retirement until both baseline commands exit 0 or Barry approves a documented exception.
- [ ] Confirm branch `chore/repository-rehabilitation` contains design commit `a74eb04` and that no existing local change was lost.

## Task 2: Make local setup idempotent and protect `.env`

**Files:**
- Modify: `laravel/composer.json`
- Modify: `laravel/.env.example`
- Modify: `laravel/phpunit.xml`
- Modify: `README.md`
- Create: `laravel/scripts/setup-local.zsh`
- Create: `laravel/tests/Feature/Setup/EnvironmentSetupTest.php`

- [ ] Write a failing PHPUnit process test that creates a minimal temporary fixture, invokes `scripts/setup-local.zsh --prepare-only`, and asserts an existing sentinel `.env` survives unchanged.
- [ ] Add a second failing case that starts without `.env` and asserts `--prepare-only` copies `.env.example` and creates `database/database.sqlite`.
- [ ] Move setup orchestration into `scripts/setup-local.zsh`; make `--prepare-only` perform only idempotent file preparation, then make the full path call `composer install` directly, install locked npm dependencies, build assets, and migrate with seed data. It must never call `composer run setup`.
- [ ] Replace the current Composer inline setup logic with a call to the zsh setup script; copy `.env` only when absent, create SQLite only when absent, and run `php artisan key:generate` only when `APP_KEY` is empty.
- [ ] Keep `.env.example` local-development focused: SQLite database, array cache, database queue only if its tables exist, and `APP_URL=http://127.0.0.1:8000`.
- [ ] Make PHPUnit default to SQLite `:memory:` without forced environment values; CI must override those values with its PostgreSQL service variables.
- [ ] Document `cd laravel && composer run setup && composer run dev` as the canonical workstation bootstrap.
- [ ] Run `cd laravel && php artisan test tests/Feature/Setup/EnvironmentSetupTest.php`.
- [ ] Run the complete `composer run setup` twice against an `rsync` copy excluding `vendor`, `node_modules`, and `.git`; confirm both runs succeed and the second run does not change `.env` or its application key.
- [ ] Commit as `fix: make local setup idempotent`.

## Task 3: Lock in the restored Inertia preview

**Files:**
- Modify: `laravel/tests/Feature/AuthenticationTest.php`
- Create: `laravel/tests/Feature/PreviewNavigationTest.php`
- Verify: `laravel/app/Providers/FortifyServiceProvider.php`
- Verify: `laravel/routes/web.php`

- [ ] Add failing tests asserting `/login` renders `Auth/Login`, `/forgot-password` renders `Auth/ForgotPassword`, and authenticated `/dashboard` renders `Dashboard`.
- [ ] Add failing redirect tests for `/admin/contacts` to `/contacts` and `/admin/organizations` to `/organizations`.
- [ ] Make only the minimum changes needed to the existing Fortify provider and web routes.
- [ ] Run `cd laravel && php artisan test tests/Feature/AuthenticationTest.php tests/Feature/PreviewNavigationTest.php`.
- [ ] Commit the preserved preview changes and tests as `fix: restore the Inertia development preview`.

## Task 4: Prove the Express prototype has no active consumer

**Files:**
- Verify: `src/index.ts`
- Verify: `src/api/**/*.ts`
- Verify: `src/models/**/*.ts`
- Verify: `src/services/dataStore.ts`
- Verify: `src/__tests__/dataStore.test.ts`
- Verify: `laravel/app/Models/**/*.php`
- Verify: `laravel/routes/api.php`
- Modify: `README.md`

- [ ] Search tracked code, workflows, package scripts, documentation, and GitHub configuration for root Express ports, `/api/dioceses`, `/api/regions`, `/api/deaneries`, `/api/parishes`, `/api/missions`, `/api/schools`, `/api/organizations`, `/api/religious-houses`, `/api/apostolates`, `/api/offices`, `/api/contacts`, and `/api/positions`.
- [ ] Run `gh api repos/schoedel-learn/drm-catholic/deployments`, `gh api repos/schoedel-learn/drm-catholic/hooks`, and `gh api repos/schoedel-learn/drm-catholic/environments` to detect external deployment consumers without exposing credentials.
- [ ] Compare the prototype models with Laravel’s `Jurisdiction`, `Organization`, `Contact`, `Position`, `Entity`, and entity-type schema.
- [ ] Confirm that the Express routes are unauthenticated in-memory CRUD prototypes, have no persistent data, and are not called by the Laravel frontend or an active deployment.
- [ ] Add a concise README architecture note explaining that the Laravel domain model supersedes the prototype’s separate entity maps; do not promise endpoint compatibility that never existed in production.
- [ ] If an active consumer is found, stop this task and add failing Laravel API tests for only the consumed contract before proceeding to Task 5.

## Task 5: Port any proven consumed contract, then retire Express

**Files:**
- Conditionally modify: `laravel/routes/api.php`
- Conditionally create: `laravel/app/Http/Controllers/Api/V1/*Controller.php`
- Conditionally create: `laravel/tests/Feature/Api/V1/*Test.php`
- Delete: `src/`
- Delete: root `package.json`
- Delete: root `package-lock.json`
- Delete: root `tsconfig.json`
- Delete: root `jest.config.js`
- Delete: root `.eslintrc*`
- Modify: root `.gitignore`
- Modify: `README.md`

- [ ] If Task 4 found a real consumer, implement its Laravel contract test-first using the existing Laravel domain models and authenticated authorization rules.
- [ ] Run the focused API tests and then `cd laravel && php artisan test`.
- [ ] Remove the root Express source, root Node manifest/lockfile, TypeScript config, Jest config, and root-only ESLint configuration.
- [ ] Remove root-only build, coverage, and TypeScript ignore entries while retaining Laravel build ignores.
- [ ] Update README commands and directory descriptions so no command refers to the removed runtime.
- [ ] Run `rg -n "npm run (build|start|test)|src/index|Express|Jest|ts-node|dist/" --glob '!docs/superpowers/**' .` and resolve every stale operational reference.
- [ ] Run `cd laravel && composer install --no-interaction && npm ci && npm run build && php artisan test`.
- [ ] Commit as `refactor: consolidate the application on Laravel`.

## Task 6: Establish supported runtimes and prove Laravel 13

**Files:**
- Modify: `laravel/composer.json`
- Modify: `laravel/composer.lock`
- Modify: `laravel/package.json`
- Modify: `laravel/package-lock.json`
- Create: `.nvmrc`
- Modify: `README.md`

- [ ] Confirm current upstream status with `for package in laravel/framework laravel/jetstream laravel/fortify laravel/sanctum inertiajs/inertia-laravel; do composer show "$package" --latest; done` and `for package in vue @inertiajs/vue3 vite @vitejs/plugin-vue tailwindcss; do npm view "$package" deprecated version time.modified; done`.
- [ ] Set Composer PHP requirement to `^8.4` and `config.platform.php` to `8.4.0`; set `package.json` engines to Node `>=24 <25` and npm `>=11`.
- [ ] Add `.nvmrc` containing `24`.
- [ ] Run `cd laravel && composer why-not laravel/framework ^13.0`.
- [ ] Run `composer require laravel/framework:^13.0 --with-all-dependencies`; upgrade maintained first-party blockers in the same transaction.
- [ ] If a direct dependency is abandoned, replace or remove it rather than patching it. If a maintained dependency does not yet support Laravel 13, restore the newest supported Laravel constraint, record the blocking package and upstream release condition in README’s requirements section, and keep PHP 8.4 if Composer permits it.
- [ ] Run `composer validate --strict`, `composer audit`, `php artisan about`, `php artisan test`, `npm ci`, and `npm run build`.
- [ ] Commit as `build: update the supported PHP and Laravel versions`.

## Task 7: Modernize Composer dependencies in compatible groups

**Files:**
- Modify: `laravel/composer.json`
- Modify: `laravel/composer.lock`
- Modify: affected Laravel source and tests only when required by documented upgrade changes

- [ ] List direct outdated dependencies with `composer outdated --direct --strict` and inspect each package’s current release and deprecation status.
- [ ] Upgrade Laravel first-party runtime packages together, then third-party runtime packages, then development packages.
- [ ] After each group run `composer validate --strict`, `composer audit`, `./vendor/bin/pint --test`, and `php artisan test`.
- [ ] Do not update transitive packages outside Composer’s resolved compatibility set and do not suppress security advisories.
- [ ] Commit each passing group separately using `build: update Laravel runtime dependencies`, `build: update third-party PHP dependencies`, and `build: update PHP development dependencies`.

## Task 8: Modernize and lint the Laravel frontend

**Files:**
- Modify: `laravel/package.json`
- Modify: `laravel/package-lock.json`
- Create: `laravel/eslint.config.js`
- Create: `laravel/.prettierignore`
- Modify: frontend files only where automated checks identify real incompatibilities

- [ ] Check maintenance status and current versions with `npm view` before changing packages.
- [ ] Upgrade Vue, Inertia, Vite, the Vue Vite plugin, Axios, and Tailwind to mutually compatible current stable releases; use Tailwind’s current Vite integration when upgrading to Tailwind 4.
- [ ] Install maintained lint/format tooling with `npm install --save-dev eslint@latest eslint-plugin-vue@latest globals@latest prettier@latest prettier-plugin-tailwindcss@latest`; accept the resolved stable majors only after the peer-dependency graph passes.
- [ ] Add scripts: `lint` for ESLint, `format` for Prettier write mode, `format:check` for Prettier verification, and retain `build`.
- [ ] Configure the installed stable ESLint flat-config API for Vue 3 browser modules and ignore `public/build`, `vendor`, and generated assets.
- [ ] Run `npm run lint`, `npm run format:check`, `npm run build`, and `npm audit --audit-level=high`.
- [ ] Commit as `build: modernize the Laravel frontend toolchain`.

## Task 9: Separate local seed data from the public demo

**Files:**
- Modify: `laravel/database/seeders/DatabaseSeeder.php`
- Create: `laravel/database/seeders/LocalDevelopmentSeeder.php`
- Create: `laravel/database/seeders/DemoSeeder.php`
- Create: `laravel/config/demo.php`
- Create: `laravel/tests/Feature/Database/DemoSeederTest.php`

- [ ] Add failing tests asserting local seeding creates the documented local user while demo seeding creates only generated `.invalid` identities and deterministic sample organizations/contacts.
- [ ] Add a failing test proving the demo seed does not contain the local preview account or domains other than `.invalid`.
- [ ] Make `DatabaseSeeder` dispatch `DemoSeeder` only when `DEMO_MODE=true`; otherwise dispatch `LocalDevelopmentSeeder`.
- [ ] Seed the shared demo login as `demo@example.invalid`; read its password from `DEMO_USER_PASSWORD` and fail loudly when demo mode lacks that value.
- [ ] Put `enabled`, `git_sha`, `user_email`, and protected demo-account settings in `config/demo.php`.
- [ ] Run `cd laravel && php artisan test tests/Feature/Database/DemoSeederTest.php`.
- [ ] Commit as `feat: add isolated demo seed data`.

## Task 10: Prevent public-demo account and email side effects

**Files:**
- Modify: `laravel/config/fortify.php`
- Create: `laravel/app/Http/Middleware/ProtectDemoAccount.php`
- Modify: `laravel/bootstrap/app.php`
- Create: `laravel/tests/Feature/Demo/DemoAuthenticationSafetyTest.php`
- Create: `laravel/tests/Feature/Demo/DemoCachedConfigurationTest.php`

- [ ] Add failing tests proving demo mode removes registration and password-reset routes.
- [ ] Add failing tests proving the shared demo account cannot update its email, password, two-factor settings, recovery codes, browser sessions, or delete itself; prove an authenticated demo request is visible to the middleware after Laravel starts the session.
- [ ] Add a control test proving normal local mode retains Fortify registration and password-reset behavior.
- [ ] Filter Fortify’s registration and reset-password features when `DEMO_MODE=true`.
- [ ] Register `ProtectDemoAccount` as named middleware and append it after Laravel’s session middleware in the web stack; resolve the user through the web guard and block enumerated Fortify/Jetstream identity methods and paths with HTTP 403 while allowing CRM data changes.
- [ ] Add separate-process tests that boot with `DEMO_MODE=true` and `false`, run `php artisan optimize:clear && php artisan optimize`, and inspect `php artisan route:list --json` so cached production configuration cannot expose registration or reset routes.
- [ ] Configure the demo runtime to use the log mailer and sync queue so no external email or background worker can be triggered.
- [ ] Run `cd laravel && php artisan test tests/Feature/Demo/DemoAuthenticationSafetyTest.php tests/Feature/Demo/DemoCachedConfigurationTest.php tests/Feature/AuthenticationTest.php tests/Feature/RegistrationTest.php`.
- [ ] Commit as `feat: harden public demo authentication`.

## Task 11: Add demo indexing and health contracts

**Files:**
- Create: `laravel/app/Http/Middleware/AddDemoNoIndexHeader.php`
- Modify: `laravel/app/Http/Controllers/Api/V1/HealthController.php`
- Modify: `laravel/config/logging.php`
- Modify: `laravel/routes/api.php`
- Modify: `laravel/bootstrap/app.php`
- Create: `laravel/tests/Feature/Demo/DemoHeadersTest.php`
- Create: `laravel/tests/Feature/Api/V1/HealthTest.php`
- Create: `laravel/tests/Feature/Demo/DemoLoggingTest.php`

- [ ] Add failing tests proving every demo web response includes `X-Robots-Tag: noindex, nofollow`, while local mode does not force the header.
- [ ] Add failing tests for `GET /api/v1/health`: HTTP 200 with `status=ok`, `database=ok`, and configured `git_sha`; return HTTP 503 when the database probe fails.
- [ ] Implement `AddDemoNoIndexHeader` and append it to web middleware.
- [ ] Make `HealthController` invokable, probe the configured database with `select 1`, report failures, and expose no secret or environment details.
- [ ] Add `GET /api/v1/health` named `api.v1.health`; retain Laravel’s `/up` liveness route.
- [ ] Configure demo logging to write Monolog JSON records to `php://stderr`; include severity, timestamp, message, and deployment SHA but never environment or secret values.
- [ ] Add tests proving demo logs are valid JSON on stderr and health-database failures are reported distinctly without secret values.
- [ ] Run `cd laravel && php artisan test tests/Feature/Demo/DemoHeadersTest.php tests/Feature/Api/V1/HealthTest.php tests/Feature/Demo/DemoLoggingTest.php`.
- [ ] Commit as `feat: add demo safety and health contracts`.

## Task 12: Build the production container and startup contract

**Files:**
- Create: `laravel/Dockerfile`
- Create: `laravel/Caddyfile`
- Create: `laravel/scripts/start-demo.sh`
- Create: `laravel/.dockerignore`
- Create: `laravel/tests/Feature/Demo/StartupConfigurationTest.php`

- [ ] Add a failing configuration test asserting the container files require `DEMO_MODE=true`, `APP_KEY`, `DEMO_USER_PASSWORD`, `DEPLOYMENT_GIT_SHA`, `DB_CONNECTION=sqlite`, and an absolute writable `DB_DATABASE`.
- [ ] Add a multi-stage Dockerfile: Node 24 asset build, Composer 2 production vendor build, and `dunglas/frankenphp:1-php8.4-bookworm` runtime with `pdo_sqlite`, `intl`, `zip`, and `opcache`.
- [ ] Define the complete Caddy contract: global `auto_https off`, site address `0.0.0.0:{$PORT:8080}`, `root * /app/public`, compression, and `php_server`.
- [ ] Copy `laravel/Caddyfile` to `/etc/frankenphp/Caddyfile` in the runtime stage before startup references that path.
- [ ] At image build time create `/tmp/drm`, `storage/framework/{cache,sessions,views}`, `storage/logs`, and `bootstrap/cache`; recursively assign only those writable paths to non-root user `app`, remove privileged-port capabilities, and run the final image as `app`.
- [ ] Make `scripts/start-demo.sh` POSIX `sh` because it runs inside the minimal container; validate required variables, create the SQLite parent/file, run `php artisan optimize:clear`, `php artisan migrate:fresh --seed --force`, `php artisan optimize`, and `exec frankenphp run --config /etc/frankenphp/Caddyfile`.
- [ ] Ensure any failed validation, migration, seed, cache, or permission step emits structured stderr and exits non-zero before FrankenPHP starts.
- [ ] Exclude `.git`, `.env*` except `.env.example`, tests, local databases, node modules, vendor, logs, and `gha-creds-*.json` from the image context.
- [ ] Run `cd laravel && php artisan test tests/Feature/Demo/StartupConfigurationTest.php`.
- [ ] Commit as `build: add the Cloud Run demo container`.

## Task 13: Smoke-test the exact container locally

**Files:**
- Create: `laravel/scripts/smoke-demo.zsh`
- Modify: `laravel/package.json`

- [ ] Add a zsh smoke script that resolves one `DEMO_IMAGE` value, building `drm-catholic-demo:local` only when none is provided; generate temporary non-production secrets, run that image on `127.0.0.1:8080`, and always remove the named container on exit.
- [ ] Assert `/up`, `/`, `/login`, and `/api/v1/health` succeed; assert health returns git SHA `local-smoke`; assert landing/login responses contain `X-Robots-Tag: noindex, nofollow`; assert `/register` and `/forgot-password` return 404.
- [ ] Assert the running UID is non-root, SQLite is writable, migrations/seed data exist, Laravel cache files are writable, logs are JSON on stderr, and forced invalid configuration prevents the server from listening.
- [ ] Add package script `demo:smoke` invoking `./scripts/smoke-demo.zsh`.
- [ ] Run `cd laravel && npm run demo:smoke`.
- [ ] Inspect `docker history "$DEMO_IMAGE" --no-trunc`, record `docker image inspect "$DEMO_IMAGE" --format '{{index .RepoDigests 0}}'` when available and the local image ID otherwise, and confirm no secret values are embedded.
- [ ] Commit as `test: add container smoke coverage`.

## Task 14: Consolidate required GitHub checks

**Files:**
- Modify: `.github/workflows/ci.yml`
- Modify: `.github/workflows/codeql.yml`

- [ ] Replace dual-runtime CI with jobs named exactly `Backend tests`, `Frontend build`, `Container smoke test`, and `Dependency audit`.
- [ ] Pin setup actions to maintained major versions; use PHP 8.4, Node 24, `composer install --no-interaction --prefer-dist --no-progress`, and `npm ci`.
- [ ] Make backend run `composer validate --strict`, `./vendor/bin/pint --test`, and `php artisan test`.
- [ ] Keep PostgreSQL 16 as the CI service, set explicit `DB_*` variables, run `php artisan migrate:fresh --force` before tests, and prove the complete migration chain independently of test traits.
- [ ] Make frontend run `npm run lint`, `npm run format:check`, and `npm run build`.
- [ ] Make dependency audit run `composer audit` and `npm audit --audit-level=high`.
- [ ] Make container smoke call the same `npm run demo:smoke` used on the workstation.
- [ ] Keep CodeQL scoped to `javascript-typescript`, because CodeQL does not support PHP; remove the matrix and name its sole job exactly `JavaScript CodeQL`.
- [ ] Run `actionlint` if already installed, and parse both workflows with the installed frontend formatter: `cd laravel && npx prettier --check ../.github/workflows/ci.yml ../.github/workflows/codeql.yml`.
- [ ] Commit as `ci: consolidate Laravel review checks`.

## Task 15: Group automated dependency updates

**Files:**
- Modify: `.github/dependabot.yml`

- [ ] Remove the deleted root npm ecosystem.
- [ ] Configure exactly four weekly entries: `composer` at `/laravel`, `npm` at `/laravel`, `github-actions` at `/`, and `docker` at `/laravel`.
- [ ] Limit each ecosystem to two open pull requests; group patch/minor production updates and development updates separately; leave majors as explicit individual reviews.
- [ ] Run a YAML parse check, verify group keys are valid for each ecosystem, and inspect the diff for exactly those four entries.
- [ ] Commit as `ci: reduce Dependabot update noise`.

## Task 16: Add GitHub-to-Cloud Run deployment

**Files:**
- Create: `.github/workflows/deploy-demo.yml`
- Create: `laravel/scripts/verify-demo.zsh`

- [ ] Add `verify-demo.zsh` to check the deployed `/up`, `/`, `/login`, `/api/v1/health`, expected SHA, no-index headers, and disabled registration/password-reset routes.
- [ ] Create workflow `Deploy DRM demo` triggered by pushes to `main` and manual dispatch, with job name `Deploy demo`, GitHub environment `demo`, permissions `contents: read` and `id-token: write`.
- [ ] Add concurrency group `drm-demo-${{ github.ref }}` with `cancel-in-progress: true`.
- [ ] Run checkout before `google-github-actions/auth@v3`; authenticate with environment variables `GCP_WIF_PROVIDER` and `GCP_DEPLOYER_SERVICE_ACCOUNT`; configure Docker with `google-github-actions/setup-gcloud@v3`.
- [ ] Build once from `laravel/Dockerfile`, tag the image with immutable `${GITHUB_SHA}`, set `DEMO_IMAGE` to that tag for `smoke-demo.zsh`, and push only that tested tag to `us-central1-docker.pkg.dev/drm-catholic-demo-1117974782/drm-demo/drm-catholic:${GITHUB_SHA}`.
- [ ] Record the pushed image digest. Detect whether the Cloud Run service exists: on later deployments capture the current 100% healthy revision and deploy with `--no-traffic`; on the first deployment create the service as the initial candidate and delete it if candidate verification fails.
- [ ] Deploy the exact digest, not the mutable tag, with a commit-specific candidate tag, unauthenticated access, port 8080, one CPU, 512 MiB memory, concurrency 20, timeout 60 seconds, min instances 0, max instances 1, and the dedicated runtime service account.
- [ ] Set non-secret runtime variables explicitly: production environment, debug false, demo mode true, SQLite path `/tmp/drm/database.sqlite`, array cache, cookie session, sync queue, log mail, `LOG_CHANNEL=stderr`, `LOG_STDERR_FORMATTER=Monolog\Formatter\JsonFormatter`, deployment SHA, and trusted proxy behavior.
- [ ] Map `APP_KEY` and `DEMO_USER_PASSWORD` from pinned Secret Manager versions; do not use `latest`.
- [ ] Resolve the candidate revision name and tag URL from Cloud Run’s `status.traffic` entry, verify the tag URL reports `${GITHUB_SHA}`, and confirm the revision references the recorded image digest.
- [ ] Query Cloud Logging for the candidate revision and require a parsed `jsonPayload` record containing the candidate SHA; confirm startup/health errors are distinguishable and neither secret value appears.
- [ ] On later deployments, promote 100% default traffic only after candidate verification. If the public-service check then fails, automatically route 100% back to the captured healthy revision and fail the workflow.
- [ ] On the first deployment, if verification fails, delete the failed service so no broken public demo remains; if it succeeds, verify default traffic and retain the first revision as the next deployment’s rollback baseline.
- [ ] Commit as `ci: deploy main to the DRM demo`.

## Task 17: Provision the dedicated Google Cloud project

**Files:**
- Modify: none
- Update after success: Infrastructure Map

- [ ] Resolve and read the canonical map with `resolve-vault-path "10 Projects/Infrastructure Map.md"` before any cloud mutation.
- [ ] Run `gcloud auth list`, `gcloud beta billing accounts list --filter=open=true`, and `gcloud organizations list`; if more than one open billing account is available, pause for Barry to choose rather than guessing.
- [ ] Create project `drm-catholic-demo-1117974782`, link the selected billing account, and set the project in the active gcloud configuration.
- [ ] Enable `run.googleapis.com`, `artifactregistry.googleapis.com`, `cloudbuild.googleapis.com`, `iamcredentials.googleapis.com`, `sts.googleapis.com`, and `secretmanager.googleapis.com`.
- [ ] Create Artifact Registry repository `drm-demo` in `us-central1`.
- [ ] Create runtime and deploy service accounts using the fixed identifiers above.
- [ ] Grant the runtime account only Secret Manager access to `drm-demo-app-key` and `drm-demo-user-password`.
- [ ] Grant the deploy account Artifact Registry writer, Cloud Run admin, and service-account-user on the runtime account; avoid project Owner/Editor.
- [ ] Create the GitHub OIDC provider with mappings `google.subject=assertion.sub`, `attribute.repository=assertion.repository`, `attribute.ref=assertion.ref`, and `attribute.workflow_ref=assertion.workflow_ref`.
- [ ] Restrict the provider condition to repository `schoedel-learn/drm-catholic`, ref `refs/heads/main`, and workflow ref `schoedel-learn/drm-catholic/.github/workflows/deploy-demo.yml@refs/heads/main`.
- [ ] Grant `roles/iam.workloadIdentityUser` only to `principalSet://iam.googleapis.com/projects/PROJECT_NUMBER/locations/global/workloadIdentityPools/github/attribute.repository/schoedel-learn/drm-catholic`; the provider condition supplies the narrower ref/workflow gate.
- [ ] Generate a Laravel application key locally without printing it, create secret `drm-demo-app-key`, add version 1 from stdin, and securely discard the temporary value.
- [ ] Generate a unique demo-only password, create `drm-demo-user-password`, add version 1 from stdin, store the password in Bitwarden under `DRM Catholic Demo`, and never write it to Git, logs, or command history.
- [ ] Capture project number, provider resource name, service accounts, secret version numbers, and service URL in the session artifact directory without secret values.

## Task 18: Configure the GitHub deployment environment

**Files:**
- Modify remotely: GitHub environment `demo`
- Modify remotely: repository variables

- [ ] Create GitHub environment `demo`.
- [ ] Restrict its deployment branch policy to protected branch `main`; retain automatic post-merge deployment without a second manual reviewer gate.
- [ ] Set environment variables `GCP_PROJECT_ID`, `GCP_REGION`, `GCP_WIF_PROVIDER`, `GCP_DEPLOYER_SERVICE_ACCOUNT`, `GCP_ARTIFACT_REPOSITORY`, `CLOUD_RUN_SERVICE`, `APP_KEY_SECRET_VERSION=1`, and `DEMO_USER_PASSWORD_SECRET_VERSION=1`.
- [ ] Do not store Google service-account JSON or application secrets in GitHub.
- [ ] Verify environment values through GitHub’s environment API without printing or storing any secret value.
- [ ] On a temporary non-main branch, remove only the `demo` environment declaration from that branch’s copy of `deploy-demo.yml`, replace cloud mutation steps with an auth-only denial assertion, and dispatch that workflow ref. Confirm the token exchange reaches WIF and is rejected, then delete the temporary branch.
- [ ] Confirm the unchanged merged-main workflow is the only accepted identity and that the repository’s persistent workflow still requires the `demo` environment.

## Task 19: Prepare the stale pull-request replacement

**Files:**
- Modify remotely: GitHub pull requests
- Modify remotely: stale remote branches only after review

- [ ] Export all open PR metadata, patch URLs, authors, branches, and check states to the session artifact directory.
- [ ] Confirm PR #2’s research is represented by the approved design, PRs #6 and #7 are superseded by live branch protection, and PR #84’s revert is superseded by the tested rehabilitation.
- [ ] Review each Dependabot PR for a security advisory not already resolved by the new lockfiles; preserve advisory references in the rehabilitation PR.
- [ ] Prepare the exact close list, reasons, and safe branch-deletion list, but make no remote PR or branch change before Barry approves the complete rehabilitation diff.

## Task 20: Protect `main`, review, merge, and verify demo

**Files:**
- Modify remotely: GitHub branch protection
- Modify: `README.md`
- Modify: Infrastructure Map
- Modify: workstation work log

- [ ] Update README with Laravel-only architecture, supported runtimes, local setup, test commands, PR workflow, and demo data/reset policy.
- [ ] Show Barry `git diff origin/main...HEAD`, commit list, test results, audit results, image scan/history result, proposed PR body, and the stale-PR close list; obtain explicit approval before pushing or opening a PR.
- [ ] Push `chore/repository-rehabilitation`, open one focused non-draft rehabilitation PR, and let CI and CodeQL run.
- [ ] Query check runs and confirm the exact contexts are `Backend tests`, `Frontend build`, `Container smoke test`, `Dependency audit`, and `JavaScript CodeQL`; fix configuration rather than weakening or bypassing a failed check.
- [ ] Configure `main` to require a pull request, one approving review, conversation resolution, a branch current with `main`, linear history, and those five exact check contexts; dismiss stale approvals and block force pushes/deletion.
- [ ] After the rehabilitation PR exists, close the approved stale PR list with truthful “superseded by rehabilitation PR” comments; do not merge stale lockfiles.
- [ ] Delete only approved bot or superseded remote branches whose PR is closed and whose commits are reachable from another ref or exported as a patch.
- [ ] Ensure the PR states what was removed, dependency changes, demo limitations, security posture, provisioning changes, checks, and rollback procedure.
- [ ] Merge only when required checks pass and review approval exists.
- [ ] Confirm the `Deploy demo` workflow serves the merged SHA, then test login with the Bitwarden demo credential without exposing it.
- [ ] Verify Cloud Run has max instances 1, the runtime service account, pinned secret versions, no unauthenticated Secret Manager access, and the previous healthy revision retained after every deployment following the first.
- [ ] Roll back by routing 100% traffic to the prior healthy revision when one exists if post-promotion verification fails; do not patch the live container.
- [ ] Add the confirmed demo URL and rollback command to README and the file resolved by `resolve-vault-path "10 Projects/Infrastructure Map.md"` in a small follow-up documentation PR; include project, region, service, Artifact Registry, identities, WIF provider, reset behavior, and secret locations.
- [ ] Update `/home/barry-schoedel/Documents/Obsidian Vault/log.md` through `log-work "DRM Catholic rehabilitation" "Consolidated DRM on Laravel with current dependencies and required GitHub review checks." "Provisioned and verified the resettable Cloud Run demo from merged main."`; never log secret values.
- [ ] Confirm the working tree is clean and the remote has no unexpected open PRs or branches.

---

## Final verification matrix

| Contract | Command or evidence | Required result |
|---|---|---|
| Workstation bootstrap | `cd laravel && composer run setup` twice | Both succeed; existing `.env` unchanged |
| Backend quality | `composer validate --strict && ./vendor/bin/pint --test && php artisan test` | Exit 0 |
| Frontend quality | `npm ci && npm run lint && npm run format:check && npm run build` | Exit 0 |
| Dependency security | `composer audit && npm audit --audit-level=high` | No unresolved high/critical advisory |
| Container | `npm run demo:smoke` | Health, auth pages, SHA, no-index, disabled signup/reset all pass |
| GitHub review | Required checks and one approval on `main` | Direct unreviewed changes blocked |
| Deployment identity | WIF provider restricted to `schoedel-learn/drm-catholic` | No JSON key |
| Demo data | `DemoSeederTest` and live inspection | Generated `.invalid` data only |
| Cloud Run | Service configuration and live probes | Max instance 1; merged SHA served |
| Rollback | Candidate deployed without traffic; prior revision and traffic command documented | Failed candidate cannot displace healthy traffic |

## Specification traceability

| Approved specification section | Implemented by |
|---|---|
| Chosen Approach / Repository Structure | Tasks 4-8 |
| Preserve Existing Local Work | Tasks 1-3 |
| Workstation Development | Tasks 2, 6-8 |
| GitHub Review Flow | Tasks 14, 18-20 |
| Continuous Integration | Tasks 14-15 |
| Cloud Run Demo | Tasks 9-13, 16-18 |
| Demo Data and Safety | Tasks 9-11 |
| Deployment Safety and Rollback | Tasks 16-17, 20 |
| Dependency Maintenance | Tasks 6-8, 15 |
| Observability | Tasks 11, 13, 16, 20 |
| Implementation Sequence | Tasks 1-20 in order |
| Acceptance Criteria | Final verification matrix |
