<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;
use Throwable;

class CloudRunJsonFormatter extends JsonFormatter
{
    private const MAX_SANITIZE_DEPTH = 9;

    private const REDACTED = '[REDACTED]';

    private const TRUNCATED = '[TRUNCATED]';

    public function format(LogRecord $record): string
    {
        $normalized = $this->normalizeRecord($record);
        $payload = [
            'severity' => $record->level->getName(),
            'timestamp' => $normalized['datetime'],
            'message' => $record->message,
            'git_sha' => $normalized['extra']['git_sha'] ?? null,
            'context' => $this->normalize($this->sanitize($record->context)),
        ];

        return $this->toJson($payload, true)."\n";
    }

    private function sanitize(mixed $value, int $depth = 0): mixed
    {
        if ($depth >= self::MAX_SANITIZE_DEPTH) {
            return self::TRUNCATED;
        }

        if ($value instanceof Throwable) {
            return ['exception_type' => $value::class];
        }

        if (is_object($value)) {
            $properties = get_object_vars($value);

            return $properties === []
                ? ['object_type' => $value::class]
                : $this->sanitize($properties, $depth + 1);
        }

        if (! is_array($value)) {
            return $value;
        }

        $sanitized = [];

        foreach ($value as $key => $item) {
            $sanitized[$key] = is_string($key) && $this->isSensitiveKey($key)
                ? self::REDACTED
                : $this->sanitize($item, $depth + 1);
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        return preg_match(
            '/password|passwd|secret|token|authorization|cookie|api[_-]?key|private[_-]?key|recovery[_-]?codes/i',
            $key,
        ) === 1;
    }
}
