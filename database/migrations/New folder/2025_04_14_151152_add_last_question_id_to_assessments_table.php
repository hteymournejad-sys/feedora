<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastQuestionIdToAssessmentsTable extends Migration
{
    public function up()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->bigInteger('last_question_id')->unsigned()->nullable()->after('holding_id');
            $table->foreign('last_question_id')->references('id')->on('questions')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['last_question_id']);
            $table->dropColumn('last_question_id');
        });
    }
}