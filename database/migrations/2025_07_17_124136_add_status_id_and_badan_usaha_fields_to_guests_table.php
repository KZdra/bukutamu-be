<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusIdAndBadanUsahaFieldsToGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->enum('type', ['perorangan', 'badan_usaha'])->default('perorangan');
            $table->string('institution')->nullable();
            $table->text('institution_address')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->foreign('status_id')->references('id')->on('status_guests')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }
    
    public function down()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn(['type', 'company_name', 'company_address', 'status_id']);
        });
    }
    
}
