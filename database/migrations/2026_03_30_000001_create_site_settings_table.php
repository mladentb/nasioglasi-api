<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Default settings
        \DB::table('site_settings')->insert([
            ['key' => 'maintenance_mode', 'value' => 'false', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'maintenance_message', 'value' => 'Sajt je trenutno u izradi. Uskoro smo tu!', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
