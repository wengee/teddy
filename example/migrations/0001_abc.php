<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-05 10:12:21 +0800
 */

use Teddy\Database\Migrations\Migration;
use Teddy\Database\Schema\Blueprint;
use Teddy\Database\Schema\Schema;

class AbcMigration_0001 extends Migration
{
    protected string $version = '1.0.0';

    protected $suffixList = ['', '_0', '_1', '_2', '_3', '_4'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->suffixList as $suffix) {
            Schema::create('abc'.$suffix, function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->suffixList as $suffix) {
            Schema::dropIfExists('abc'.$suffix);
        }
    }
}
