<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->integer('score')->nullable()->after('improvement_opportunities');
            $table->integer('result')->nullable()->after('score');
            $table->integer('excel_version')->default(1)->after('result');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['score', 'result', 'excel_version']);
        });
    }
}