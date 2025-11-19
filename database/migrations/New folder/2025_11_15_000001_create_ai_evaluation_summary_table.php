<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_evaluation_summary', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ارتباط با شرکت / هلدینگ و گروه ارزیابی
            $table->foreignId('company_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('company_alias')->nullable();

            $table->foreignId('holding_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('assessment_group_id')
                ->constrained('assessment_groups')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // اطلاعات زمانی
            $table->dateTime('evaluation_date');
            $table->string('period_label', 50)->nullable(); // مثل "آبان ۱۴۰۳" یا "2025-Q1"

            // امتیاز کل
            $table->decimal('final_score', 5, 2)->nullable(); // 0..100

            // امتیاز هر حوزه
            $table->decimal('score_it_governance', 5, 2)->nullable();
            $table->decimal('score_info_security', 5, 2)->nullable();
            $table->decimal('score_infrastructure', 5, 2)->nullable();
            $table->decimal('score_it_support', 5, 2)->nullable();
            $table->decimal('score_applications', 5, 2)->nullable();
            $table->decimal('score_digital_transformation', 5, 2)->nullable();
            $table->decimal('score_intelligence', 5, 2)->nullable();

            // زیرحوزه‌ها
            $table->json('subcategory_scores')->nullable();

            // سطح بلوغ و میانگین‌ها
            $table->unsignedTinyInteger('overall_maturity_level')->nullable(); // 1..5
            $table->decimal('maturity_level_1_avg', 5, 2)->nullable();
            $table->decimal('maturity_level_2_avg', 5, 2)->nullable();
            $table->decimal('maturity_level_3_avg', 5, 2)->nullable();
            $table->decimal('maturity_level_4_avg', 5, 2)->nullable();
            $table->decimal('maturity_level_5_avg', 5, 2)->nullable();

            // شمارنده‌ها
            $table->unsignedInteger('strength_count')->default(0);
            $table->unsignedInteger('risk_high_count')->default(0);
            $table->unsignedInteger('risk_medium_count')->default(0);
            $table->unsignedInteger('risk_low_count')->default(0);
            $table->unsignedInteger('improvement_count')->default(0);

            // اطلاعات تکمیلی
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('answered_questions')->default(0);

            $table->timestamps();

            // ایندکس‌ها
            $table->index(['company_id', 'evaluation_date'], 'idx_ai_eval_company_date');
            $table->index(['company_id', 'assessment_group_id'], 'idx_ai_eval_company_group');
            $table->index('assessment_group_id', 'idx_ai_eval_group');
            $table->index('holding_id', 'idx_ai_eval_holding');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_evaluation_summary');
    }
};
