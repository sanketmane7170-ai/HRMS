<?php

namespace Modules\AgenticAI\Tools;

use Modules\Payroll\Entities\EmployeeTax;
use Exception;

class ConfigurePayrollTaxTool extends BaseTool
{
    public function name(): string
    {
        return 'configure_payroll_tax';
    }

    public function description(): string
    {
        return 'Create or update payroll tax rules. Tax units can be "percentage" or "fixed".';
    }

    public function isSensitive(): bool
    {
        return true;
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'taxtype' => ['type' => 'string', 'description' => 'Name of the tax (e.g., VAT, Income Tax).'],
                'taxunit' => ['type' => 'string', 'enum' => ['percentage', 'fixed']],
                'taxamount' => ['type' => 'number', 'description' => 'The tax value.'],
                'id' => ['type' => 'integer', 'description' => 'Required for updates.']
            ],
            'required' => ['taxtype', 'taxunit', 'taxamount']
        ];
    }

    public function execute(array $args): mixed
    {
        try {
            if (!empty($args['id'])) {
                $tax = EmployeeTax::findOrFail($args['id']);
                $tax->update($args);
                $message = "Tax rule '{$tax->taxtype}' updated.";
            } else {
                $tax = EmployeeTax::create($args);
                $message = "Tax rule '{$tax->taxtype}' created.";
            }

            return [
                'success' => true,
                'message' => $message,
                'tax_id' => $tax->id
            ];

        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
