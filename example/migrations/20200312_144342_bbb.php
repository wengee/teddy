<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-12 15:56:00 +0800
 */

use Teddy\Database\Schema\Schema;
use Teddy\Database\Schema\Blueprint;
use Teddy\Database\Migrations\Migration;

class Bbb20200312144342 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('test', function (Blueprint $table): void {
            $table->dropColumn('val2');
            $table->integer('val3')->after('val1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
}
