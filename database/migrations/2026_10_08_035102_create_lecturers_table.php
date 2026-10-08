<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lecturers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('academic_title')->nullable();
            $table->string('nidn')->nullable()->unique();
            $table->string('nip')->nullable();
            $table->string('institution')->nullable();
            $table->string('faculty')->nullable();
            $table->string('department')->nullable();
            $table->text('expertise')->nullable();
            $table->boolean('is_pddikti_verified')->default(false);
            $table->timestamp('pddikti_verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lecturers');
    }
};
