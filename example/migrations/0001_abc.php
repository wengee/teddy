<?php

use Teddy\Database\Schema\Schema;
use Teddy\Database\Schema\Blueprint;
use Teddy\Database\Migrations\Migration;

class AbcMigration_0001 extends Migration
{
    protected $version = '1.0.0';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('abc', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abc');
    }
}
