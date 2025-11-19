<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('text'); // متن سوال
            $table->string('domain')->nullable(); // حوزه سوالات
            $table->string('subcategory')->nullable(); // دسته‌بندی
            $table->integer('weight'); // وزن سوال
            // دامنه کاربرد
            $table->boolean('applicable_small')->default(false);
            $table->boolean('applicable_medium')->default(false);
            $table->boolean('applicable_large')->default(false);
            $table->boolean('applicable_manufacturing')->default(false);
            $table->boolean('applicable_service')->default(false);
            $table->boolean('applicable_distribution')->default(false);
            $table->boolean('applicable_investment')->default(false);
            // توضیحات اضافی
            $table->text('description')->nullable();
            $table->text('risks')->nullable();
            $table->text('strengths')->nullable();
            $table->text('current_status')->nullable();
            $table->text('improvement_opportunities')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
}