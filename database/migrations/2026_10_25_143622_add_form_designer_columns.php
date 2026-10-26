<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->string('registration_mode')->default('builtin')->after('total_slots');
        $table->string('external_form_url')->nullable()->after('registration_mode');
        $table->json('form_theme')->nullable()->after('external_form_url');
    });

    Schema::table('event_fields', function (Blueprint $table) {
        $table->text('description')->nullable()->after('label');
        $table->string('width')->default('full')->after('type');
    });
}

public function down(): void
{
    Schema::table('event_fields', function (Blueprint $table) {
        $table->dropColumn(['description', 'width']);
    });

    Schema::table('events', function (Blueprint $table) {
        $table->dropColumn(['registration_mode', 'external_form_url', 'form_theme']);
    });
}
};
