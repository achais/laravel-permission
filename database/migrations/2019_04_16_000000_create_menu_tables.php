<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        Schema::create($tableNames['menus'], function (Blueprint $table) use ($tableNames) {
            $table->increments('id');
            $table->unsignedInteger('level')->default(0);
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('parent_path');
            $table->string('name');
            $table->string('url')->default('#');
            $table->unsignedInteger('type')->default(0);
            $table->string('sign')->nullable();
            $table->string('remark')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')
                ->references('id')
                ->on($tableNames['menus'])
                ->onDelete('cascade');
        });

        Schema::create($tableNames['role_has_menus'], function (Blueprint $table) use ($tableNames, $columnNames) {

            $table->unsignedInteger('menu_id');
            $table->unsignedInteger('role_id');

            $table->foreign('menu_id')
                ->references('id')
                ->on($tableNames['menus'])
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');

            $table->primary(['menu_id', 'role_id']);
        });

        Schema::create($tableNames['menu_table'], function (Blueprint $table) use ($tableNames, $columnNames) {

            $table->increments('id');
            $table->unsignedInteger('menu_id');
            $table->string('column_config');
            $table->string('remark');
            $table->unsignedInteger('sort');
            $table->boolean('is_show');
            $table->timestamps();

            $table->foreign('menu_id')
                ->references('id')
                ->on($tableNames['menus'])
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableNames = config('permission.table_names');

        Schema::drop($tableNames['role_has_menus']);
        Schema::drop($tableNames['menus']);
        Schema::drop($tableNames['menu_table']);
    }
}
