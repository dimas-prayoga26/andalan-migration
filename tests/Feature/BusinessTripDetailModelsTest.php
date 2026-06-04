<?php

namespace Tests\Feature;

use App\Models\BusinessTripCashAdvance;
use App\Models\BusinessTripExpenseItem;
use App\Models\BusinessTripLifecycleLog;
use App\Models\BusinessTripReimbursement;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BusinessTripDetailModelsTest extends TestCase
{
    public function test_business_trip_detail_models_are_defined_with_relationships(): void
    {
        foreach ([
            BusinessTripExpenseItem::class => [
                "protected \$table = 'business_trip_expense_items';",
                'use SoftDeletes;',
                "'amount' => 'decimal:2'",
                'public function businessTrip(): BelongsTo',
            ],
            BusinessTripCashAdvance::class => [
                "protected \$table = 'business_trip_cash_advances';",
                'use SoftDeletes;',
                "'date_needed' => 'date'",
                "'amount_requested' => 'decimal:2'",
                'public function requestedBy(): BelongsTo',
                'public function approvedBy(): BelongsTo',
            ],
            BusinessTripReimbursement::class => [
                "protected \$table = 'business_trip_reimbursements';",
                'use SoftDeletes;',
                "'expense_date' => 'date'",
                "'amount' => 'decimal:2'",
                'public function requestedBy(): BelongsTo',
                'public function approvedBy(): BelongsTo',
            ],
            BusinessTripLifecycleLog::class => [
                "protected \$table = 'business_trip_lifecycle_logs';",
                "'status' => 'waiting'",
                "'happened_at' => 'datetime'",
                "'metadata' => 'array'",
                'public function actor(): BelongsTo',
            ],
        ] as $modelClass => $expectedFragments) {
            $model = new $modelClass;
            $modelSource = File::get((new \ReflectionClass($modelClass))->getFileName());

            $this->assertFalse($model->incrementing);
            $this->assertSame('string', $model->getKeyType());
            $this->assertSame([], $model->getGuarded());

            foreach ($expectedFragments as $expectedFragment) {
                $this->assertStringContainsString($expectedFragment, $modelSource);
            }
        }
    }

    public function test_business_trip_model_has_detail_relationships(): void
    {
        $businessTripModel = File::get(app_path('Models/BusinessTrip.php'));

        foreach ([
            'use SoftDeletes;',
            "'start_date' => 'date'",
            "'end_date' => 'date'",
            'public function expenseItems(): HasMany',
            'BusinessTripExpenseItem::class',
            'public function supervisor(): BelongsTo',
            "'supervisor_employee_id'",
            'public function cashAdvances(): HasMany',
            'BusinessTripCashAdvance::class',
            'public function reimbursements(): HasMany',
            'BusinessTripReimbursement::class',
            'public function lifecycleLogs(): HasMany',
            'BusinessTripLifecycleLog::class',
        ] as $expectedFragment) {
            $this->assertStringContainsString($expectedFragment, $businessTripModel);
        }
    }
}
