public function up()
{
    Schema::create('security_logs', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->timestamp('timestamp')->useCurrent();
        $table->bigInteger('user_id')->unsigned()->nullable();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        $table->string('action');
        $table->text('details')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('security_logs');
}