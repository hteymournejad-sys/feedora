public function up()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->string('receipt_image')->nullable()->after('payment_id');
        $table->string('invoice_file')->nullable()->after('receipt_image');
    });
}

public function down()
{
    Schema::table('payments', function (Blueprint $table) {
        $table->dropColumn(['receipt_image', 'invoice_file']);
    });
}