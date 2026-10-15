<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMfaChallengesTable extends Migration
{
    public function up(): void
    {
        Schema::create('mfa_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mfa_setting_id')->constrained('mfa_settings')->cascadeOnDelete();
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->string('session_id')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('used_at')->nullable()->index();
            $table->timestamps();
            $table->index(['user_type', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mfa_challenges');
    }
}
