<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMfaSettingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('mfa_settings', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->string('provider')->default('totp');
            $table->text('secret')->nullable();
            $table->boolean('enabled')->default(false)->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->unsignedBigInteger('last_used_timestep')->nullable();
            $table->timestamps();
            $table->unique(['user_type', 'user_id', 'provider']);
            $table->index(['user_type', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mfa_settings');
    }
}
