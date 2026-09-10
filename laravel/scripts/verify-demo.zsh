#!/usr/bin/env zsh
#
# verify-demo.zsh — Verify a deployed DRM demo base URL against the public
# demo contract, failing loudly on the first violation.
#
# Against a supplied base URL it asserts:
#   - GET /up                -> 200 (framework liveness)
#   - GET /                  -> 200 and the demo noindex header
#   - GET /login             -> 200 and the demo noindex header
#   - GET /api/v1/health     -> 200, status "ok", and git_sha == expected SHA
#   - GET /register          -> 404 (registration disabled in demo mode)
#   - GET /forgot-password   -> 404 (password reset disabled in demo mode)
#
# The interface is intentionally explicit so the deploy workflow can call it
# for both a commit-specific candidate tag URL (pre-promotion) and the public
# service URL (post-promotion). It relies only on the demo contract, never on
# any hostname shape.
#
# Usage:
#   verify-demo.zsh --base-url URL --expected-sha SHA [--timeout SECONDS] [--ready-timeout SECONDS]
#   verify-demo.zsh URL SHA
#
# Environment fallbacks (flags win):
#   DEMO_BASE_URL, EXPECTED_SHA, VERIFY_HTTP_TIMEOUT, VERIFY_READY_TIMEOUT
#
# Requires: zsh, curl, jq.

set -euo pipefail

log()  { print -r --  "[verify-demo] $*" }
warn() { print -ru2 -- "[verify-demo] $*" }
fail() { print -ru2 -- "[verify-demo] FAIL: $*"; exit 1 }

usage() {
    print -ru2 -- "Usage: ${0:t} --base-url URL --expected-sha SHA [--timeout SECONDS] [--ready-timeout SECONDS]"
    print -ru2 -- "       ${0:t} URL SHA"
}

# --- Preflight: required external commands ------------------------------
REQUIRED_COMMANDS=(curl jq)
missing_commands=()
for cmd in "${REQUIRED_COMMANDS[@]}"; do
    command -v -- "$cmd" >/dev/null 2>&1 || missing_commands+=("$cmd")
done
if (( ${#missing_commands[@]} > 0 )); then
    fail "required command(s) not found on PATH: ${missing_commands[*]}"
fi

# --- Parse arguments ----------------------------------------------------
BASE_URL="${DEMO_BASE_URL:-}"
EXPECTED_SHA="${EXPECTED_SHA:-}"
HTTP_TIMEOUT="${VERIFY_HTTP_TIMEOUT:-10}"
READY_TIMEOUT="${VERIFY_READY_TIMEOUT:-60}"
positional=()

while (( $# > 0 )); do
    case "$1" in
        -u|--base-url)
            [[ $# -ge 2 ]] || { usage; fail "--base-url requires a value" }
            BASE_URL="$2"; shift 2 ;;
        -s|--expected-sha)
            [[ $# -ge 2 ]] || { usage; fail "--expected-sha requires a value" }
            EXPECTED_SHA="$2"; shift 2 ;;
        -t|--timeout)
            [[ $# -ge 2 ]] || { usage; fail "--timeout requires a value" }
            HTTP_TIMEOUT="$2"; shift 2 ;;
        -r|--ready-timeout)
            [[ $# -ge 2 ]] || { usage; fail "--ready-timeout requires a value" }
            READY_TIMEOUT="$2"; shift 2 ;;
        -h|--help)
            usage; exit 0 ;;
        --)
            shift; positional+=("$@"); break ;;
        -*)
            usage; fail "unknown option: $1" ;;
        *)
            positional+=("$1"); shift ;;
    esac
done

# Positional fallbacks: URL then SHA.
if [[ -z "$BASE_URL" && ${#positional[@]} -ge 1 ]]; then
    BASE_URL="${positional[1]}"
fi
if [[ -z "$EXPECTED_SHA" && ${#positional[@]} -ge 2 ]]; then
    EXPECTED_SHA="${positional[2]}"
fi

[[ -n "$BASE_URL" ]]     || { usage; fail "a base URL is required (--base-url)" }
[[ -n "$EXPECTED_SHA" ]] || { usage; fail "an expected SHA is required (--expected-sha)" }

# Numeric, bounded timeouts only.
[[ "$HTTP_TIMEOUT" == <-> ]]  || fail "--timeout must be a positive integer (got '${HTTP_TIMEOUT}')"
[[ "$READY_TIMEOUT" == <-> ]] || fail "--ready-timeout must be a positive integer (got '${READY_TIMEOUT}')"

# Normalise: drop a single trailing slash so path joins are predictable.
BASE_URL="${BASE_URL%/}"

log "Verifying ${BASE_URL} (expecting git_sha=${EXPECTED_SHA})"

# --- HTTP helpers -------------------------------------------------------
# All requests are individually bounded by connect and total timeouts.
http_status() {
    local url="$1"
    curl -sS -o /dev/null -w '%{http_code}' \
        --connect-timeout "$HTTP_TIMEOUT" --max-time "$HTTP_TIMEOUT" \
        "$url" 2>/dev/null || print -r -- '000'
}

assert_status() {
    local url="$1" expected="$2" desc="$3" code
    code="$(http_status "$url")"
    [[ "$code" == "$expected" ]] \
        || fail "${desc}: expected HTTP ${expected} but got ${code} (${url})"
    log "OK: ${desc} -> HTTP ${code}"
}

assert_noindex() {
    local url="$1" desc="$2" headers
    headers="$(curl -sS -D - -o /dev/null \
        --connect-timeout "$HTTP_TIMEOUT" --max-time "$HTTP_TIMEOUT" \
        "$url" 2>/dev/null | tr -d '\r')" \
        || fail "${desc}: could not fetch response headers (${url})"
    print -r -- "$headers" | grep -qi '^X-Robots-Tag:[[:space:]]*noindex, nofollow$' \
        || fail "${desc}: missing 'X-Robots-Tag: noindex, nofollow' header (${url})"
    log "OK: ${desc} carries the demo noindex header"
}

# --- Bounded readiness wait --------------------------------------------
# A freshly promoted or cold-starting revision may need a moment; poll /up
# until it answers 200 or the overall readiness budget is exhausted.
wait_for_ready() {
    local deadline=$(( SECONDS + READY_TIMEOUT )) code
    while :; do
        code="$(http_status "${BASE_URL}/up")"
        if [[ "$code" == '200' ]]; then
            log "Ready: /up answered 200"
            return 0
        fi
        if (( SECONDS >= deadline )); then
            fail "timed out after ${READY_TIMEOUT}s waiting for /up to become ready (last status ${code})"
        fi
        sleep 1
    done
}

# --- Run the contract ---------------------------------------------------
wait_for_ready

assert_status "${BASE_URL}/up"    200 '/up liveness'
assert_status "${BASE_URL}/"      200 '/ landing page'
assert_status "${BASE_URL}/login" 200 '/login page'

assert_noindex "${BASE_URL}/"      '/ landing page'
assert_noindex "${BASE_URL}/login" '/login page'

assert_status "${BASE_URL}/register"        404 '/register (registration disabled)'
assert_status "${BASE_URL}/forgot-password" 404 '/forgot-password (password reset disabled)'

# Readiness output must both succeed and prove the deployed commit.
health_body="$(curl -sS \
    --connect-timeout "$HTTP_TIMEOUT" --max-time "$HTTP_TIMEOUT" \
    -w $'\n%{http_code}' "${BASE_URL}/api/v1/health" 2>/dev/null)" \
    || fail "/api/v1/health: request failed (${BASE_URL}/api/v1/health)"

health_code="${health_body##*$'\n'}"
health_json="${health_body%$'\n'*}"

[[ "$health_code" == '200' ]] \
    || fail "/api/v1/health: expected HTTP 200 but got ${health_code}"

print -r -- "$health_json" | jq -e '.status == "ok"' >/dev/null 2>&1 \
    || fail "/api/v1/health: readiness status was not \"ok\" (body: ${health_json})"

reported_sha="$(print -r -- "$health_json" | jq -r '.git_sha // empty' 2>/dev/null)"
[[ -n "$reported_sha" ]] \
    || fail "/api/v1/health: readiness output did not include git_sha (body: ${health_json})"
[[ "$reported_sha" == "$EXPECTED_SHA" ]] \
    || fail "/api/v1/health: expected git_sha '${EXPECTED_SHA}' but readiness reported '${reported_sha}'"

log "OK: /api/v1/health readiness reports git_sha=${reported_sha}"

log "All demo checks passed for ${BASE_URL}"
