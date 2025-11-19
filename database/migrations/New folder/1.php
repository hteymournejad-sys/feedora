use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDomainToAssessmentsTable extends Migration
{
    public function up()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->string('domain')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('domain');
        });
    }
}