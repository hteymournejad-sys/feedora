<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportLstmDataset extends Command
{
    protected $signature = 'ai:lstm-export-dataset
                            {--path=ai/lstm_dataset.csv : مسیر خروجی داخل storage/app}';

    protected $description = 'خروجی گرفتن دیتاست LSTM از جدول ai_lstm_features به صورت CSV';

    public function handle()
    {
        $path = $this->option('path');

        // اگر دایرکتوری ai وجود ندارد، بساز
        if (!Storage::exists(dirname($path))) {
            Storage::makeDirectory(dirname($path));
        }

        $fullPath = Storage::path($path);

        $this->info("در حال ساخت دیتاست LSTM در: {$fullPath}");

        // کوئری: همه‌ی رکوردها به ترتیب شرکت و time_index
        $rows = DB::table('ai_lstm_features')
            ->select([
                'company_id',
                'assessment_group_id',
                'evaluation_date',
                'time_index',

                'final_score',
                'f_score_it_governance',
                'f_score_info_security',
                'f_score_infrastructure',
                'f_score_it_support',
                'f_score_applications',
                'f_score_digital_transformation',
                'f_score_intelligence',
                'f_maturity_1',
                'f_maturity_2',
                'f_maturity_3',
                'f_maturity_4',
                'f_maturity_5',
                'f_risk_high',
                'f_risk_medium',
                'f_risk_low',
                'f_strength_count',
                'f_improvement_count',
                'f_it_budget',
                'f_it_expenditure',
                'f_full_time_it_staff',
                'f_training_hours_per_it_staff',

                'target_final_score',
                'target_maturity_level',

                'target_score_info_security_y2',
                'target_score_info_security_y3',
                'target_score_info_security_y5',
                'target_final_score_y2',
                'target_final_score_y3',
                'target_final_score_y5',
                'target_risk_high_y2',
                'target_risk_high_y3',
                'target_risk_high_y5',
                'target_strength_count_y2',
                'target_strength_count_y3',
                'target_strength_count_y5',
                'target_maturity_level_y2',
                'target_maturity_level_y3',
                'target_maturity_level_y5',
            ])
            ->orderBy('company_id')
            ->orderBy('time_index')
            ->get();

        // ساخت فایل CSV
        $fp = fopen($fullPath, 'w');

        // هدر
        if ($rows->count() > 0) {
            fputcsv($fp, array_keys((array) $rows[0]));
        }

        foreach ($rows as $row) {
            fputcsv($fp, (array) $row);
        }

        fclose($fp);

        $this->info("✅ دیتاست با موفقیت ساخته شد.");
        $this->info("می‌توانید فایل را به Python بدهید: {$fullPath}");

        return 0;
    }
}
