<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('projects', function (Blueprint $table) {  
            $table->increments('id');
            $table->integer('organization_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->string('name_for_client')->nullable();      // Название для клиента
            $table->string('sms_sender')->nullable();           // Имя отправителя смс
            $table->integer('hold')->nullable();                // Холд (дней)
            $table->string('url')->nullable();                  // URL
           
            $table->boolean('is_private')->nullable();          // Приват
            $table->boolean('is_call_tracking')->nullable();    // Коллтрекинг
            $table->boolean('is_authors')->nullable();          // Авторский оффер?
            $table->boolean('is_resale')->nullable();           // Ресейл?
            $table->boolean('is_postcode_info')->nullable();    // Использовать подсказки по индексу?
            
            $table->integer('category_id')->nullable();         // ID категории
            $table->string('image')->nullable();                // Логотип
            $table->integer('gender')->nullable();              // Пол
            $table->integer('postclick')->nullable();           // Постклик
            
            $table->json('age')->nullable();             	    // Возраст 
            $table->string('import_id')->nullable();            // ID импорта

            $table->string('name_en')->nullable();              // Наименование на английском
            $table->string('countries')->nullable();
            //$table->integer('sex')->nullable();
            $table->integer('kc_category')->nullable();
            
            $table->integer('project_category_kc_id')->nullable();

            $table->boolean('replica')->nullable();              // Реплика
            $table->text('operator_notes')->nullable();        // Примечания для оператора

            $table->timestamps();

//            $table->string('client_name')->nullable();
//            $table->integer('parent_id')->nullable();
//            $table->text('description')->nullable();
//            $table->integer('hold')->nullable();
//            $table->string('sms_sender')->nullable();
//            $table->integer('is_work')->nullable();
//            $table->string('script_img')->nullable();
//            $table->integer('prognos')->nullable();
//            $table->integer('create_user')->nullable();
//            $table->integer('update_user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
