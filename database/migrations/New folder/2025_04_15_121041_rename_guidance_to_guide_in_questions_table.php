<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameGuidanceToGuideInQuestionsTable extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('guidance', 'guide');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('guide', 'guidance');
        });
    }
}