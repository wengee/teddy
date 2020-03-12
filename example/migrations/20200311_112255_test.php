<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-12 15:49:00 +0800
 */

use Teddy\Database\Schema\Schema;
use Teddy\Database\Schema\Blueprint;
use Teddy\Database\Migrations\Migration;

class Test20200311112255 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('test', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('val1');
            $table->string('val2');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test');
    }
}
