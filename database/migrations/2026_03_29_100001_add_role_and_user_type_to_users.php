<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role: super_admin, admin, user
            $table->string('role', 20)->default('user')->after('is_admin');

            // User type: individual (fizičko lice), company (pravno lice)
            $table->string('user_type', 20)->default('individual')->after('role');

            // Company fields (pravno lice)
            $table->string('company_name')->nullable()->after('user_type');
            $table->string('pib', 20)->nullable()->after('company_name'); // poreski identifikacioni broj
            $table->string('maticni_broj', 20)->nullable()->after('pib'); // matični broj
            $table->string('company_address')->nullable()->after('maticni_broj');
            $table->string('company_city')->nullable()->after('company_address');
            $table->string('company_country', 2)->nullable()->after('company_city');

            $table->index('role');
            $table->index('user_type');
        });

        // Migrate existing is_admin to role
        \DB::table('users')->where('is_admin', true)->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['user_type']);
            $table->dropColumn([
                'role', 'user_type', 'company_name', 'pib',
                'maticni_broj', 'company_address', 'company_city', 'company_country',
            ]);
        });
    }
};
