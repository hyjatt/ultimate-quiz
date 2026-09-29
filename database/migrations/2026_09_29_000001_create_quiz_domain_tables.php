<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->unsignedInteger('min_points')->unique();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('prompt');
            $table->json('options');
            $table->string('correct_option', 1);
            $table->string('difficulty', 10)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('difficulty', 10)->index();
            $table->string('status', 20)->default('started')->index();
            $table->unsignedTinyInteger('question_count')->default(10);
            $table->unsignedTinyInteger('correct_answers')->default(0);
            $table->unsignedTinyInteger('accuracy')->default(0);
            $table->unsignedInteger('points_awarded')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('quiz_attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('selected_option', 1)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->unique(['quiz_attempt_id', 'question_id']);
            $table->unique(['quiz_attempt_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_questions');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('ranks');
    }
};
