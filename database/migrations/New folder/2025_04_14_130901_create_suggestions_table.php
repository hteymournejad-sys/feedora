public function up()
{
    Schema::create('suggestions', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->bigInteger('user_id')->unsigned();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        $table->string('subject');
        $table->text('suggestion');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('suggestions');
}