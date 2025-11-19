<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfileTable extends Migration
{
    public function up()
    {
        Schema::create('user_profile', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->enum('company_activity', ['تولیدی', 'خدماتی', 'پخش', 'سرمایه‌گذاری'])->default('تولیدی');
            $table->integer('total_employees')->nullable();
            $table->enum('company_size', ['بزرگ', 'متوسط', 'کوچک'])->default('بزرگ');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profile');
    }
}