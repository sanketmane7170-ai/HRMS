<?php

namespace Modules\AgenticAI\Interfaces;

interface ToolInterface
{
    /**
     * The unique name of the tool (e.g., 'get_leave_balance').
     */
    public function name(): string;

    /**
     * A clear description of what the tool does for the AI.
     */
    public function description(): string;

    /**
     * JSON Schema for the tool's parameters.
     * Use this to tell OpenAI structure of arguments.
     * 
     * @return array
     */
    public function schema(): array;

    /**
     * Whether this tool requires human approval.
     */
    public function isSensitive(): bool;

    /**
     * Execute the tool with the given arguments.
     * 
     * @param array $args
     * @return mixed
     */
    public function execute(array $args): mixed;
}
