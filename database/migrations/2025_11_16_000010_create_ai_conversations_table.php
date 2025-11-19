<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->bigIncrements('id');

            // اگر کاربر لاگین باشد
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // نوع سناریو: single_company, company_compare, ...
            $table->string('scenario_type', 50)->index();

            // شرکت‌ها
            $table->unsignedBigInteger('company_a_id')->nullable()->index();
            $table->string('company_a_alias')->nullable();

            $table->unsignedBigInteger('company_b_id')->nullable()->index();
            $table->string('company_b_alias')->nullable();

            // متن سؤال و پاسخ
            $table->text('question');
            $table->longText('answer')->nullable();

            // پرومپت سیستم و یوزر (برای دیباگ و بهبود)
            $table->longText('system_prompt')->nullable();
            $table->longText('user_prompt')->nullable();

            // متن کانتکست‌ها (خلاصه‌شده، نه الزاماً همه بلوک‌ها)
            $table->longText('context_a')->nullable();
            $table->longText('context_b')->nullable();

            // بلوک‌ها/متادیتا به صورت JSON (در صورت نیاز)
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
