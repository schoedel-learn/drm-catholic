<?php

namespace Tests\Feature\Ci;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Guards the critical, security-sensitive invariants of the Cloud Run
 * deployment workflow. Behavioural shell logic lives in verify-demo.zsh and is
 * exercised for real in VerifyDemoScriptTest; here we assert the workflow's
 * structure and its rollback / first-deploy-cleanup contract, which cannot be
 * run offline against real Cloud Run.
 */
class DeployDemoWorkflowTest extends TestCase
{
    /**
     * @var array<string, mixed>
     */
    private static array $config;

    private static string $raw;

    private static string $deployRun;

    public static function setUpBeforeClass(): void
    {
        $path = dirname(__DIR__, 4).'/.github/workflows/deploy-demo.yml';
        self::assertFileExists($path);

        self::$raw = (string) file_get_contents($path);
        self::$config = Yaml::parseFile($path);
        self::$deployRun = self::deployStepRun();
    }

    public function test_workflow_identity_and_triggers(): void
    {
        $this->assertSame('Deploy DRM demo', self::$config['name']);

        $on = self::$config['on'];
        $this->assertSame(['main'], $on['push']['branches']);
        $this->assertArrayHasKey('workflow_dispatch', $on);
    }

    public function test_permissions_are_minimal(): void
    {
        $this->assertSame(
            ['contents' => 'read', 'id-token' => 'write'],
            self::$config['permissions'],
        );
    }

    public function test_concurrency_cancels_superseded_runs(): void
    {
        $this->assertSame('drm-demo-${{ github.ref }}', self::$config['concurrency']['group']);
        $this->assertTrue(self::$config['concurrency']['cancel-in-progress']);
    }

    public function test_job_targets_the_demo_environment(): void
    {
        $job = self::job();

        $this->assertSame('Deploy demo', $job['name']);
        $this->assertSame('demo', $job['environment']);
        $this->assertSame('ubuntu-latest', $job['runs-on']);
    }

    public function test_checkout_runs_before_authentication(): void
    {
        $checkout = self::stepIndexUsing('actions/checkout@');
        $auth = self::stepIndexUsing('google-github-actions/auth@v3');

        $this->assertNotNull($checkout, 'a checkout step is required');
        $this->assertNotNull($auth, 'the auth@v3 step is required');
        $this->assertLessThan($auth, $checkout, 'checkout must precede auth');
    }

    public function test_authentication_uses_wif_environment_values(): void
    {
        $auth = self::stepUsing('google-github-actions/auth@v3');
        $this->assertNotNull($auth);

        $this->assertSame('${{ vars.GCP_WIF_PROVIDER }}', $auth['with']['workload_identity_provider']);
        $this->assertSame('${{ vars.GCP_DEPLOYER_SERVICE_ACCOUNT }}', $auth['with']['service_account']);

        $this->assertNotNull(self::stepUsing('google-github-actions/setup-gcloud@v3'));
    }

    public function test_image_is_built_once_and_smoke_tested_before_push(): void
    {
        $build = self::stepByName('Build demo image');
        $this->assertStringContainsString('docker build -f laravel/Dockerfile', $build['run']);

        // The smoke test must run the exact tag that will be pushed (no rebuild).
        $smoke = self::stepByName('Smoke-test the built image');
        $this->assertSame('${{ steps.image.outputs.image_tag }}', $smoke['env']['DEMO_IMAGE']);
        $this->assertStringContainsString('smoke-demo.zsh', $smoke['run']);

        $push = self::stepByName('Push the tested image and record its digest');
        $this->assertStringContainsString('docker push', $push['run']);
        $this->assertStringContainsString('sha256:', $push['run']);

        // Ordering: build -> smoke -> push.
        $this->assertLessThan(
            self::stepIndexByName('Smoke-test the built image'),
            self::stepIndexByName('Build demo image'),
        );
        $this->assertLessThan(
            self::stepIndexByName('Push the tested image and record its digest'),
            self::stepIndexByName('Smoke-test the built image'),
        );
    }

    public function test_deploy_uses_the_immutable_digest_not_a_mutable_tag(): void
    {
        $this->assertStringContainsString('--image "${IMAGE_DIGEST_REF}"', self::$deployRun);
        // The candidate tag URL is what verify-demo checks pre-promotion.
        $this->assertStringContainsString('--tag "${CANDIDATE_TAG}"', self::$deployRun);
        // Guard against ever deploying the mutable :sha tag.
        $this->assertStringNotContainsString('--image "${IMAGE_TAG}"', self::$deployRun);
    }

    public function test_cloud_run_resource_contract(): void
    {
        foreach ([
            '--allow-unauthenticated',
            '--port 8080',
            '--cpu 1',
            '--memory 512Mi',
            '--concurrency 20',
            '--timeout 60',
            '--min-instances 0',
            '--max-instances 1',
            '--service-account "${RUNTIME_SA}"',
        ] as $needle) {
            $this->assertStringContainsString($needle, self::$deployRun, "missing Cloud Run flag: {$needle}");
        }

        $this->assertStringContainsString(
            'RUNTIME_SA="drm-demo-runtime@${GCP_PROJECT_ID}.iam.gserviceaccount.com"',
            self::$deployRun,
        );
    }

    public function test_non_secret_runtime_variables_are_set(): void
    {
        foreach ([
            'APP_ENV=production',
            'APP_DEBUG=false',
            'DEMO_MODE=true',
            'DB_CONNECTION=sqlite',
            'DB_DATABASE=/tmp/drm/database.sqlite',
            'CACHE_STORE=array',
            'SESSION_DRIVER=cookie',
            'QUEUE_CONNECTION=sync',
            'MAIL_MAILER=log',
            'LOG_CHANNEL=stderr',
            // Source keeps escaped backslashes that bash collapses to single
            // backslashes at runtime (final value: Monolog\Formatter\JsonFormatter).
            'LOG_STDERR_FORMATTER=Monolog\\\\Formatter\\\\JsonFormatter',
            'DEPLOYMENT_GIT_SHA=${GITHUB_SHA}',
            'APP_URL=${SERVICE_URL}',
        ] as $needle) {
            $this->assertStringContainsString($needle, self::$deployRun, "missing runtime var: {$needle}");
        }
    }

    public function test_secrets_are_mapped_from_pinned_versions_and_reject_latest(): void
    {
        $this->assertStringContainsString(
            'APP_KEY=${APP_KEY_SECRET_NAME}:${APP_KEY_SECRET_VERSION}',
            self::$deployRun,
        );
        $this->assertStringContainsString(
            'DEMO_USER_PASSWORD=${DEMO_USER_PASSWORD_SECRET_NAME}:${DEMO_USER_PASSWORD_SECRET_VERSION}',
            self::$deployRun,
        );

        $job = self::job();
        $this->assertSame('drm-demo-app-key', $job['env']['APP_KEY_SECRET_NAME']);
        $this->assertSame('drm-demo-user-password', $job['env']['DEMO_USER_PASSWORD_SECRET_NAME']);

        // Non-numeric / latest / empty versions must be rejected before mutation.
        $this->assertStringContainsString('^[1-9][0-9]*$', self::$deployRun);
    }

    public function test_candidate_is_resolved_from_status_traffic(): void
    {
        $this->assertStringContainsString('.status.traffic[]?', self::$deployRun);
        $this->assertStringContainsString('select(.tag == $t)', self::$deployRun);
        // Confirm the candidate references the recorded digest and SHA.
        $this->assertStringContainsString('${REVISION_IMAGE}" == "${IMAGE_DIGEST_REF}', self::$deployRun);
        $this->assertStringContainsString('DEPLOYMENT_GIT_SHA', self::$deployRun);
    }

    public function test_structured_logging_is_checked_without_exposing_secrets(): void
    {
        $this->assertStringContainsString('gcloud logging read', self::$deployRun);
        $this->assertStringContainsString('jsonPayload', self::$deployRun);
        // Only counts/severities are computed; raw payloads and secret values
        // are never fetched or printed.
        $this->assertStringNotContainsString('gcloud secrets versions access', self::$deployRun);
    }

    public function test_later_deploy_stages_without_traffic_then_promotes(): void
    {
        $this->assertStringContainsString('--no-traffic', self::$deployRun);
        $this->assertStringContainsString('gcloud run services update-traffic', self::$deployRun);
        $this->assertStringContainsString('--to-revisions "${CANDIDATE_REVISION}=100"', self::$deployRun);
    }

    public function test_failure_finalizer_rolls_back_or_deletes(): void
    {
        // A trap makes cleanup run under set -e and step failure.
        $this->assertStringContainsString('trap finalize EXIT', self::$deployRun);
        // Later deploy: route 100% back to the captured healthy baseline.
        $this->assertStringContainsString('--to-revisions "${BASELINE_REVISION}=100"', self::$deployRun);
        // First deploy: delete the failed service so no broken demo remains.
        $this->assertStringContainsString('gcloud run services delete "${CLOUD_RUN_SERVICE}"', self::$deployRun);
        // The baseline is captured before any mutation, gated on prior existence.
        $this->assertStringContainsString('BASELINE_REVISION=', self::$deployRun);
        $this->assertStringContainsString('SERVICE_EXISTS', self::$deployRun);
    }

    /**
     * @return array<string, mixed>
     */
    private static function job(): array
    {
        return self::$config['jobs']['deploy'];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function steps(): array
    {
        return self::job()['steps'];
    }

    private static function deployStepRun(): string
    {
        $step = self::stepByName('Deploy candidate, verify, and promote');

        return $step['run'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function stepByName(string $name): array
    {
        foreach (self::steps() as $step) {
            if (($step['name'] ?? null) === $name) {
                return $step;
            }
        }

        self::fail("workflow step not found: {$name}");
    }

    private static function stepIndexByName(string $name): int
    {
        foreach (self::steps() as $index => $step) {
            if (($step['name'] ?? null) === $name) {
                return $index;
            }
        }

        self::fail("workflow step not found: {$name}");
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function stepUsing(string $uses): ?array
    {
        foreach (self::steps() as $step) {
            if (str_contains((string) ($step['uses'] ?? ''), $uses)) {
                return $step;
            }
        }

        return null;
    }

    private static function stepIndexUsing(string $uses): ?int
    {
        foreach (self::steps() as $index => $step) {
            if (str_contains((string) ($step['uses'] ?? ''), $uses)) {
                return $index;
            }
        }

        return null;
    }
}
