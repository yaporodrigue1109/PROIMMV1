<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('propriete_proximites')) {
            Schema::create('propriete_proximites', function (Blueprint $table) {
                $table->uuid('propriete_proximite_id')->primary();
                $table->uuid('propriete_id')->index();
                $table->unsignedBigInteger('proximite_id')->index();
                $table->decimal('distance', 10, 2)->nullable();
                $table->string('unite', 5)->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->string('deleted_by')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('propriete_proximites');
    }
};
