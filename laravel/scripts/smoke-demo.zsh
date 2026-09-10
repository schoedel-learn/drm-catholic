#!/usr/bin/env zsh
#
# smoke-demo.zsh — Smoke-tests the exact production demo container image.
#
# Resolves a single DEMO_IMAGE value (building drm-catholic-demo:local only
# when none is supplied), runs it exactly as the demo entrypoint expects on
# 127.0.0.1:8080 with freshly generated, non-production secrets, and asserts
# the container behaves per the Task 12 contract:
#   - /up, /, /login and /api/v1/health all succeed and health reports
#     git_sha "local-smoke"
#   - non-API responses carry the demo-mode noindex header
#   - /register and /forgot-password are disabled (404) in demo mode
#   - the process runs as a non-root UID, SQLite/cache directories are
#     writable, migrations/seed data are present, and structured JSON is
#     emitted on stderr
#   - invalid configuration is rejected before a listener ever starts
#
# Safe to re-run repeatedly (collision-resistant container names, cleanup
# traps on success/failure/interrupt) and intended for CI use (Task 14).
#
# Requires: zsh, docker, curl, jq, openssl.

set -euo pipefail

log() { print -r -- "[smoke-demo] $*" }
fail() { print -ru2 -- "[smoke-demo] FAIL: $*"; exit 1 }

SCRIPT_DIR=${0:A:h}
LARAVEL_DIR=${SCRIPT_DIR:h}
LOCAL_IMAGE_TAG='drm-catholic-demo:local'
HOST='127.0.0.1'
PORT='8080'
READY_TIMEOUT=60
GIT_SHA='local-smoke'

SUFFIX="$$-${RANDOM}-$(date +%s)"
CONTAINER_NAME="drm-demo-smoke-${SUFFIX}"
INVALID_CONTAINER_NAME="drm-demo-smoke-invalid-${SUFFIX}"

cleanup() {
    local exit_status=$?
    docker rm -f "$CONTAINER_NAME" >/dev/null 2>&1 || true
    docker rm -f "$INVALID_CONTAINER_NAME" >/dev/null 2>&1 || true
    return $exit_status
}
trap cleanup EXIT
# INT/TERM must terminate the script after cleanup, not merely run the
# handler and resume execution (zsh/bash traps don't exit on their own).
trap 'cleanup; exit 130' INT
trap 'cleanup; exit 143' TERM

# Prints the container's logs with the generated secrets scrubbed, so
# failure diagnostics never leak APP_KEY or DEMO_USER_PASSWORD.
show_logs_redacted() {
    local name=$1
    docker logs "$name" 2>&1 \
        | sed -e "s#${APP_KEY}#[REDACTED_APP_KEY]#g" -e "s#${DEMO_USER_PASSWORD}#[REDACTED_DEMO_PASSWORD]#g"
}

assert_status() {
    local url=$1 expected=$2 desc=$3
    local code
    code=$(curl -s -o /dev/null -w '%{http_code}' "$url")
    [[ "$code" == "$expected" ]] || fail "$desc expected HTTP $expected but got $code"
    log "OK: $desc -> HTTP $code"
}

assert_header() {
    local url=$1 desc=$2
    local headers
    headers=$(curl -sD - -o /dev/null "$url" | tr -d '\r')
    print -r -- "$headers" | grep -qi '^X-Robots-Tag: noindex, nofollow$' \
        || fail "$desc missing 'X-Robots-Tag: noindex, nofollow' header"
    log "OK: $desc carries the demo noindex header"
}

# --- Resolve DEMO_IMAGE exactly once -----------------------------------
if [[ -n "${DEMO_IMAGE:-}" ]]; then
    log "Using provided DEMO_IMAGE=${DEMO_IMAGE} (not rebuilding or retagging)"
else
    log "DEMO_IMAGE not provided; building ${LOCAL_IMAGE_TAG} from ${LARAVEL_DIR}"
    docker build -t "$LOCAL_IMAGE_TAG" "$LARAVEL_DIR"
    DEMO_IMAGE="$LOCAL_IMAGE_TAG"
fi
readonly DEMO_IMAGE
log "Resolved DEMO_IMAGE=${DEMO_IMAGE}"

# --- Generate temporary, non-production secrets ------------------------
# Kept only in environment variables; never interpolated into argv or
# printed. `docker run -e NAME` (no `=value`) pulls the value straight from
# this shell's environment so it never appears in process listings.
APP_KEY="base64:$(openssl rand -base64 32)"
DEMO_USER_PASSWORD="$(openssl rand -base64 24)"
export APP_KEY DEMO_USER_PASSWORD

# --- Refuse to start if the loopback port is already occupied ----------
if ss -ltn "( sport = :${PORT} )" 2>/dev/null | grep -q LISTEN; then
    fail "127.0.0.1:${PORT} is already in use; free the port before running the smoke test"
fi

# --- Start the container exactly as the demo entrypoint expects --------
log "Starting ${CONTAINER_NAME} from ${DEMO_IMAGE} on ${HOST}:${PORT}"
docker run -d \
    --name "$CONTAINER_NAME" \
    -p "${HOST}:${PORT}:${PORT}" \
    -e DEMO_MODE=true \
    -e APP_KEY \
    -e DEMO_USER_PASSWORD \
    -e DEPLOYMENT_GIT_SHA="$GIT_SHA" \
    -e DB_CONNECTION=sqlite \
    -e DB_DATABASE=/tmp/drm/database.sqlite \
    "$DEMO_IMAGE" >/dev/null

# --- Wait boundedly for readiness ---------------------------------------
log "Waiting up to ${READY_TIMEOUT}s for readiness on http://${HOST}:${PORT}/up"
SECONDS=0
until curl -fsS -o /dev/null "http://${HOST}:${PORT}/up" 2>/dev/null; do
    if (( SECONDS >= READY_TIMEOUT )); then
        log 'Container logs (secrets redacted):'
        show_logs_redacted "$CONTAINER_NAME"
        fail "timed out waiting for /up after ${READY_TIMEOUT}s"
    fi
    sleep 1
done
log "Container became ready after ${SECONDS}s"

# --- Endpoint contract ---------------------------------------------------
assert_status "http://${HOST}:${PORT}/up" 200 '/up liveness'
assert_status "http://${HOST}:${PORT}/" 200 '/ landing page'
assert_status "http://${HOST}:${PORT}/login" 200 '/login page'
assert_status "http://${HOST}:${PORT}/api/v1/health" 200 '/api/v1/health readiness'
assert_status "http://${HOST}:${PORT}/register" 404 '/register (disabled in demo mode)'
assert_status "http://${HOST}:${PORT}/forgot-password" 404 '/forgot-password (disabled in demo mode)'

health_body=$(curl -s "http://${HOST}:${PORT}/api/v1/health")
reported_sha=$(print -r -- "$health_body" | jq -r '.git_sha // empty')
[[ "$reported_sha" == "$GIT_SHA" ]] \
    || fail "expected health git_sha '${GIT_SHA}' but got '${reported_sha}'"
log "OK: /api/v1/health reports git_sha=${reported_sha}"

assert_header "http://${HOST}:${PORT}/" '/ landing page'
assert_header "http://${HOST}:${PORT}/login" '/login page'

# --- Runtime process/permission checks ----------------------------------
running_uid=$(docker exec "$CONTAINER_NAME" id -u)
[[ -n "$running_uid" && "$running_uid" != '0' ]] \
    || fail "container is running as root (uid=${running_uid})"
log "OK: container runs as non-root uid ${running_uid}"

docker exec "$CONTAINER_NAME" sh -c \
    '[ -f /tmp/drm/database.sqlite ] && [ -w /tmp/drm/database.sqlite ]' \
    || fail 'SQLite database file is missing or not writable'
log 'OK: SQLite database file exists and is writable'

docker exec "$CONTAINER_NAME" sh -c '
    set -e
    for d in /app/storage/framework/cache /app/storage/framework/sessions \
             /app/storage/framework/views /app/bootstrap/cache; do
        touch "$d/.smoke-write-test"
        rm -f "$d/.smoke-write-test"
    done
    [ -s /app/bootstrap/cache/config.php ]
    ls /app/bootstrap/cache/routes-*.php >/dev/null 2>&1
' || fail 'Laravel cache directories are not writable, or optimize caches are missing'
log 'OK: Laravel cache directories are writable and optimize caches are present'

# --- Migration/seed data: deterministic demo account + seeded domain data
seed_probe=$(docker exec -i "$CONTAINER_NAME" php <<'PHP'
<?php
$pdo = new PDO('sqlite:/tmp/drm/database.sqlite');

$stmt = $pdo->prepare('select count(*) as c from users where email = ?');
$stmt->execute(['demo@example.invalid']);
$demoUsers = (int) $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$organizations = (int) $pdo->query('select count(*) as c from organizations')
    ->fetch(PDO::FETCH_ASSOC)['c'];

$migrationsRan = (int) $pdo->query(
    "select count(*) as c from sqlite_master where type = 'table' and name = 'migrations'"
)->fetch(PDO::FETCH_ASSOC)['c'] > 0;

echo json_encode([
    'demo_user_count' => $demoUsers,
    'organization_count' => $organizations,
    'migrations_ran' => $migrationsRan,
]);
PHP
)
print -r -- "$seed_probe" | jq -e \
    '.demo_user_count == 1 and .organization_count >= 2 and .migrations_ran == true' \
    >/dev/null 2>&1 || fail "migration/seed probe failed: ${seed_probe}"
log 'OK: migrations ran and deterministic demo account + seeded organizations are present'

# --- Structured JSON logging on stderr -----------------------------------
# Force a real application error (an unreadable SQLite file) rather than
# grepping for a brace, so we capture and parse a genuine emitted record.
docker exec "$CONTAINER_NAME" chmod 000 /tmp/drm/database.sqlite
assert_status "http://${HOST}:${PORT}/api/v1/health" 503 '/api/v1/health while database is unreadable'

app_log_line=$(docker logs "$CONTAINER_NAME" 2>&1 | while IFS= read -r line; do
    if print -r -- "$line" | jq -e 'has("severity")' >/dev/null 2>&1; then
        print -r -- "$line"
    fi
done | tail -n 1)

[[ -n "$app_log_line" ]] || fail 'no structured JSON application log record was found on stderr'
print -r -- "$app_log_line" | jq -e \
    '.severity == "ERROR" and (.message | test("database probe failed"; "i")) and .git_sha == "local-smoke"' \
    >/dev/null 2>&1 || fail "structured log record did not match expected shape: ${app_log_line}"
log 'OK: a structured JSON error record was emitted on stderr'

# --- Invalid configuration must never start a listener -------------------
# Stop the healthy container first so a false positive can't occur from it
# already owning the loopback port.
docker rm -f "$CONTAINER_NAME" >/dev/null 2>&1
log 'Stopped the healthy container; verifying invalid configuration is rejected'

set +e
docker run \
    --name "$INVALID_CONTAINER_NAME" \
    -p "${HOST}:${PORT}:${PORT}" \
    -e DEMO_MODE=true \
    -e APP_KEY \
    -e DEMO_USER_PASSWORD \
    -e DEPLOYMENT_GIT_SHA="$GIT_SHA" \
    -e DB_CONNECTION=postgres \
    -e DB_DATABASE=/tmp/drm/database.sqlite \
    "$DEMO_IMAGE" >/dev/null 2>&1
invalid_exit=$?
set -e

[[ "$invalid_exit" -ne 0 ]] \
    || fail 'container with invalid DB_CONNECTION unexpectedly exited successfully'
log "OK: invalid-configuration container exited with status ${invalid_exit}"

if curl -s -o /dev/null --max-time 3 "http://${HOST}:${PORT}/up"; then
    fail 'invalid-configuration container unexpectedly accepted connections'
fi
log 'OK: no listener was ever started for the invalid configuration'

docker logs "$INVALID_CONTAINER_NAME" 2>&1 | grep -q '"event":"demo_startup_failed"' \
    || fail 'invalid-configuration container did not emit the expected startup-failure record'
log 'OK: invalid-configuration container emitted a structured startup-failure record'

docker rm -f "$INVALID_CONTAINER_NAME" >/dev/null 2>&1

# --- Confirm no generated secret is baked into the image ------------------
history_output=$(docker history "$DEMO_IMAGE" --no-trunc)
inspect_output=$(docker image inspect "$DEMO_IMAGE")

for secret_value in "$APP_KEY" "$DEMO_USER_PASSWORD"; do
    if print -r -- "$history_output" | grep -qF -- "$secret_value"; then
        fail 'a generated secret value was found embedded in `docker history`'
    fi
    if print -r -- "$inspect_output" | grep -qF -- "$secret_value"; then
        fail 'a generated secret value was found embedded in `docker image inspect`'
    fi
done
log 'OK: no generated secret value is embedded in the image history or metadata'

# --- Report resolved image identity --------------------------------------
repo_digest=$(docker image inspect "$DEMO_IMAGE" --format '{{json .RepoDigests}}' \
    | jq -r 'if length > 0 then .[0] else empty end')
if [[ -n "$repo_digest" ]]; then
    log "Resolved image identity (RepoDigest): ${repo_digest}"
else
    image_id=$(docker image inspect "$DEMO_IMAGE" --format '{{.Id}}')
    log "Resolved image identity (local image ID, no RepoDigest available): ${image_id}"
fi

log 'All smoke checks passed.'
