public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->integer('total_employees')->nullable()->after('company_type');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('total_employees');
    });
}