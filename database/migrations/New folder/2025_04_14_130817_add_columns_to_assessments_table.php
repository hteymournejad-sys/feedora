public function up()
{
    Schema::table('assessments', function (Blueprint $table) {
        $table->integer('excel_version')->default(1)->after('performance_percentage');
        $table->bigInteger('holding_id')->unsigned()->nullable()->after('excel_version');
        $table->foreign('holding_id')->references('id')->on('holdings')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('assessments', function (Blueprint $table) {
        $table->dropForeign(['holding_id']);
        $table->dropColumn(['excel_version', 'holding_id']);
    });
}