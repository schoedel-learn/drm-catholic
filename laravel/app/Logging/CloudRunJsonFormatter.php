<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class CloudRunJsonFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $normalized = $this->normalizeRecord($record);
        $payload = [
            'severity' => $record->level->getName(),
            'timestamp' => $normalized['datetime'],
            'message' => $record->message,
            'git_sha' => $normalized['extra']['git_sha'] ?? null,
            'context' => $normalized['context'],
        ];

        return $this->toJson($payload, true)."\n";
    }
}
