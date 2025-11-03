<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('platform_settings', static function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique()->comment('Platform identifier (shared, web, mobile, ios, android, etc.)');
            $table->json('settings')->comment('Platform-specific settings as JSON');
            $table->timestamps();

            $table->index('platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
