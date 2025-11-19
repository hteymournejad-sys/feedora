<?php

namespace App\Imports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // دیباگ برای چک کردن داده‌های ورودی
        \Log::info('Row data:', $row);

        return new Question([
            'text' => $row['text'] ?? '',
            'domain' => $row['domain'] ?? 'نامشخص',
            'subcategory' => $row['subcategory'] ?? null,
            'weight' => (int)($row['weight'] ?? 1),
            'applicable_small' => filter_var($row['applicable_small'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_medium' => filter_var($row['applicable_medium'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_large' => filter_var($row['applicable_large'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_manufacturing' => filter_var($row['applicable_manufacturing'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_service' => filter_var($row['applicable_service'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_distribution' => filter_var($row['applicable_distribution'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_investment' => filter_var($row['applicable_investment'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_project' => filter_var($row['applicable_project'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_university' => filter_var($row['applicable_university'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_research' => filter_var($row['applicable_research'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_hospital' => filter_var($row['applicable_hospital'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'applicable_banking' => filter_var($row['applicable_banking'] ?? '0', FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            'description' => $row['description'] ?? null,
            'guide' => $row['guide'] ?? null,
            'risks' => $row['risks'] ?? null,
            'strengths' => $row['strengths'] ?? null,
            'current_status' => $row['current_status'] ?? null,
            'improvement_opportunities' => $row['improvement_opportunities'] ?? null,
            'Maturity_level' => $row['Maturity_level'] ?? null,
        ]);
    }
}