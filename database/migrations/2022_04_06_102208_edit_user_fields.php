<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds project-specific attributes to the generated users table
 */
class EditUserFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->binary('profile_picture')->nullable();
            $table->unsignedBigInteger('organization_id');
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
            $table->dropColumn([
                'first_name', 
                'last_name',
                'profile_picture',
                'organization_id'
            ]);
        });
    }
}
