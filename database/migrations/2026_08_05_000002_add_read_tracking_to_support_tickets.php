<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->timestamp('agence_read_at')->nullable()->after('resolved_at');
            $table->timestamp('admin_read_at')->nullable()->after('agence_read_at');
        });

        DB::table('support_tickets')->update(['agence_read_at' => now(), 'admin_read_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['agence_read_at', 'admin_read_at']);
        });
    }
};
