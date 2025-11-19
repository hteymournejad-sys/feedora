<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_insight_items', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ارتباط با شرکت و دوره ارزیابی
            $table->foreignId('company_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('assessment_group_id')
                ->constrained('assessment_groups')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->dateTime('evaluation_date');

            // نوع آیتم
            $table->enum('item_type', [
                'risk',
                'strength',
                'improvement',
                'summary',
                'maturity_summary',
                'swot_strength',
                'swot_weakness',
                'swot_opportunity',
                'swot_threat',
            ]);

            // شدت برای ریسک‌ها
            $table->enum('severity', ['high', 'medium', 'low'])->nullable();

            // حوزه و زیرحوزه
            $table->string('domain')->nullable();
            $table->string('subcategory')->nullable();

            // سطح بلوغ (در صورت ارتباط)
            $table->unsignedTinyInteger('maturity_level')->nullable();

            // ارتباط با سؤال
            $table->foreignId('question_id')
                ->nullable()
                ->constrained('questions')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedTinyInteger('score')->nullable();
            $table->unsignedTinyInteger('weight')->nullable();

            // متن
            $table->string('title');
            $table->longText('content');

            // تگ‌ها برای RAG
            $table->json('tags')->nullable();

            // منبع
            $table->string('source', 50)->nullable(); // 'from_question', 'ai_generated', 'manual', ...

            $table->timestamps();

            // ایندکس‌ها
            $table->index(['company_id', 'item_type'], 'idx_ai_item_company_type');
            $table->index(['company_id', 'severity'], 'idx_ai_item_company_severity');
            $table->index(['company_id', 'domain'], 'idx_ai_item_company_domain');
            $table->index(['assessment_group_id', 'item_type'], 'idx_ai_item_group_type');
            $table->index('question_id', 'idx_ai_item_question');
            $table->index('evaluation_date', 'idx_ai_item_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_insight_items');
    }
};
