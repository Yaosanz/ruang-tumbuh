<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->integer('score')->nullable()->change();
            $table->integer('max_score')->nullable()->change();
            $table->integer('percentage')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->integer('score')->nullable(false)->change();
            $table->integer('max_score')->nullable(false)->change();
            $table->integer('percentage')->nullable(false)->change();
        });
    }
};