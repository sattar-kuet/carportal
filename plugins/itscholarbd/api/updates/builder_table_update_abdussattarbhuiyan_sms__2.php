<?php namespace AbdusSattarBhuiyan\Sms\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAbdussattarbhuiyanSms2 extends Migration
{
    public function up()
    {
        Schema::table('abdussattarbhuiyan_sms_', function($table)
        {
            $table->dropColumn('token');
        });
    }
    
    public function down()
    {
        Schema::table('abdussattarbhuiyan_sms_', function($table)
        {
            $table->text('token');
        });
    }
}
