public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->bigInteger('holding_id')->unsigned()->nullable()->after('self_assessments');
        $table->foreign('holding_id')->references('id')->on('holdings')->onDelete('set null');
        $table->integer('failed_login_attempts')->default(0)->after('password');
        $table->timestamp('last_failed_login')->nullable()->after('failed_login_attempts');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['holding_id']);
        $table->dropColumn(['holding_id', 'failed_login_attempts', 'last_failed_login']);
    });
}