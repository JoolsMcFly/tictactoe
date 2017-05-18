<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('size_played')->default('{}');
            $table->integer('total_time')->default(0);
            $table->integer('total_moves')->default(0);
            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('size_played');
            $table->dropColumn('total_time');
            $table->dropColumn('total_moves');
            $table->dropColumn('wins');
            $table->dropColumn('losses');
        });
    }
}