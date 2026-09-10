<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class AddDeploymentContext implements ProcessorInterface
{
    public function __construct(private readonly ?string $gitSha = null) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['git_sha'] = $this->gitSha;

        return $record;
    }
}
