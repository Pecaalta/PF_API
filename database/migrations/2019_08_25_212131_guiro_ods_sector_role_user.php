<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GuiroOdsSectorRoleUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guiro', function (Blueprint $table) {
            $table->unsignedinteger('id');
            $table->integer('id_padre');
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_guiro', function (Blueprint $table) {
            $table->integer('id_company');
            $table->integer('id_guiro');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->primary(['id_company', 'id_guiro']);
        });
        
        Schema::create('company_ods', function (Blueprint $table) {
            $table->integer('id_company');
            $table->integer('ods');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->primary(['id_company', 'ods']);
        });

        Schema::create('company_sector', function (Blueprint $table) {
            $table->integer('id_company');
            $table->integer('id_sector');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->primary(['id_company', 'id_sector']);
        });

        Schema::create('company_role', function (Blueprint $table) {
            $table->unsignedBiginteger('id');
            $table->integer('id_company');

            $table->string('key_add');

            $table->boolean('edit_info')->default(false);
            $table->boolean('delete_company')->default(false);
            $table->boolean('remove_user')->default(false);
            $table->boolean('edit_role')->default(false);
            
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
        
        Schema::create('company_user', function (Blueprint $table) {
            $table->integer('id_company');
            $table->integer('id_user');
            $table->integer('id_role');

            $table->boolean('edit_info')->default(false);
            $table->boolean('delete_company')->default(false);
            $table->boolean('remove_user')->default(false);
            $table->boolean('edit_role')->default(false);

            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->primary(['id_company', 'id_user']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('guiro');
        Schema::drop('company_guiro');
        Schema::drop('company_ods');
        Schema::drop('company_sector');
        Schema::drop('company_role');
        Schema::drop('company_user');
    }
}
