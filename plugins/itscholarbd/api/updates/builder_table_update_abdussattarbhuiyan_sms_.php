<?php namespace AbdusSattarBhuiyan\Sms\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateAbdussattarbhuiyanSms extends Migration
{
    public function up()
    {
        Schema::table('abdussattarbhuiyan_sms_', function($table)
        {
            $table->text('phone')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('abdussattarbhuiyan_sms_', function($table)
        {
            $table->string('phone', 191)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
