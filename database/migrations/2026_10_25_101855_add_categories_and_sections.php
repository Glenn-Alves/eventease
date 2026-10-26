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
    if (! Schema::hasTable('event_categories')) {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedInteger('slots');
            $table->text('inclusions')->nullable();
            $table->date('registration_ends_at')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    if (! Schema::hasColumn('event_fields', 'section')) {
        Schema::table('event_fields', function (Blueprint $table) {
            $table->string('section')->default('Registration Details')->after('event_id');
        });
    }

    if (! Schema::hasColumn('registrations', 'event_category_id')) {
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('event_category_id')->nullable()->after('event_id')
                ->constrained('event_categories')->nullOnDelete();
        });
    }
}

public function down(): void
{
    Schema::table('registrations', function (Blueprint $table) {
        $table->dropConstrainedForeignId('event_category_id');
    });

    Schema::table('event_fields', function (Blueprint $table) {
        $table->dropColumn('section');
    });

    Schema::dropIfExists('event_categories');
}
    
    
};
