<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('category')->nullable();
            $table->string('assessment_type')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('passing_score')->nullable();
            $table->boolean('is_published')->default(false);
            $table->json('interpretation_ranges')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
