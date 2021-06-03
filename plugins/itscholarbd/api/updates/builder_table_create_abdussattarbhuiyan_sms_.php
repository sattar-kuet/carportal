<?php namespace AbdusSattarBhuiyan\Sms\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateAbdussattarbhuiyanSms extends Migration
{
    public function up()
    {
        Schema::create('abdussattarbhuiyan_sms_', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('phone');
            $table->text('message');
            $table->text('token');
            $table->integer('user_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('abdussattarbhuiyan_sms_');
    }
}
