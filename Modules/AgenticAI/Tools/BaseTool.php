<?php

namespace Modules\AgenticAI\Tools;

use Modules\AgenticAI\Interfaces\ToolInterface;

abstract class BaseTool implements ToolInterface
{
    public function isSensitive(): bool
    {
        return false;
    }

    abstract public function name(): string;
    abstract public function description(): string;
    abstract public function schema(): array;
    abstract public function execute(array $args): mixed;
}
