<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUserProfileTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('user_profile');
    }

    public function down()
    {
        Schema::create('user_profile', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->enum('company_activity', ['تولیدی', 'خدماتی', 'پخش', 'سرمایه‌گذاری'])->default('تولیدی');
            $table->integer('total_employees')->nullable();
            $table->enum('company_size', ['بزرگ', 'متوسط', 'کوچک'])->default('بزرگ');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
}