<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_lstm_features', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ارتباط با شرکت و دوره
            $table->foreignId('company_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('assessment_group_id')
                ->constrained('assessment_groups')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->dateTime('evaluation_date');

            // امتیاز کل
            $table->decimal('final_score', 5, 2)->nullable();

            // امتیاز ۷ حوزه
            $table->decimal('f_score_it_governance', 5, 2)->nullable();
            $table->decimal('f_score_info_security', 5, 2)->nullable();
            $table->decimal('f_score_infrastructure', 5, 2)->nullable();
            $table->decimal('f_score_it_support', 5, 2)->nullable();
            $table->decimal('f_score_applications', 5, 2)->nullable();
            $table->decimal('f_score_digital_transformation', 5, 2)->nullable();
            $table->decimal('f_score_intelligence', 5, 2)->nullable();

            // سطوح بلوغ
            $table->decimal('f_maturity_1', 5, 2)->nullable();
            $table->decimal('f_maturity_2', 5, 2)->nullable();
            $table->decimal('f_maturity_3', 5, 2)->nullable();
            $table->decimal('f_maturity_4', 5, 2)->nullable();
            $table->decimal('f_maturity_5', 5, 2)->nullable();

            // شمارنده‌ها
            $table->unsignedInteger('f_risk_high')->default(0);
            $table->unsignedInteger('f_risk_medium')->default(0);
            $table->unsignedInteger('f_risk_low')->default(0);
            $table->unsignedInteger('f_strength_count')->default(0);
            $table->unsignedInteger('f_improvement_count')->default(0);

            // چند Feature غیر فنی (اختیاری)
            $table->decimal('f_it_budget', 15, 2)->nullable();
            $table->decimal('f_it_expenditure', 15, 2)->nullable();
            $table->unsignedInteger('f_full_time_it_staff')->nullable();
            $table->decimal('f_training_hours_per_it_staff', 8, 2)->nullable();

            // بردار کامل Feature
            $table->json('feature_vector')->nullable();

            // نسخه تعریف Featureها
            $table->unsignedSmallInteger('feature_version')->default(1);

            $table->timestamps();

            // ایندکس‌ها
            $table->index(['company_id', 'evaluation_date'], 'idx_ai_lstm_company_date');
            $table->index(['company_id', 'assessment_group_id'], 'idx_ai_lstm_company_group');
            $table->index('assessment_group_id', 'idx_ai_lstm_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_lstm_features');
    }
};
