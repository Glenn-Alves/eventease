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
    Schema::table('users', function (Blueprint $table) {
        $table->string('account_status')->default('approved')->after('role');
        $table->text('status_note')->nullable()->after('account_status');
        $table->timestamp('reviewed_at')->nullable()->after('status_note');
        $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
    });

    Schema::table('events', function (Blueprint $table) {
        $table->string('approval_status')->default('approved')->after('total_slots');
        $table->text('approval_note')->nullable()->after('approval_status');
        $table->timestamp('reviewed_at')->nullable()->after('approval_note');
        $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')->constrained('users')->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->dropConstrainedForeignId('reviewed_by');
        $table->dropColumn(['approval_status', 'approval_note', 'reviewed_at']);
    });

    Schema::table('users', function (Blueprint $table) {
        $table->dropConstrainedForeignId('reviewed_by');
        $table->dropColumn(['account_status', 'status_note', 'reviewed_at']);
    });
}
};
