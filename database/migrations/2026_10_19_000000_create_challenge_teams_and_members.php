<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('challenge_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedTinyInteger('size');
            $table->string('captain_email');
            $table->string('access_token', 64)->unique();
            $table->timestamps();
        });

        Schema::create('challenge_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('challenge_teams')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('role')->default('swimmer');
            $table->string('access_token', 64)->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('nationality')->nullable();
            $table->text('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('terms_version')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['team_id', 'email']);
        });

        Schema::create('challenge_member_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('challenge_teams')->cascadeOnDelete();
            $table->foreignId('staff_user_id')->constrained('users');
            $table->string('old_name');
            $table->string('old_email');
            $table->string('new_name');
            $table->string('new_email');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_member_replacements');
        Schema::dropIfExists('challenge_members');
        Schema::dropIfExists('challenge_teams');
    }
};
