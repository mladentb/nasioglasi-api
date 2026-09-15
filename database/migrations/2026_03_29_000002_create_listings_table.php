<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 12, 2)->nullable();
            $table->string('price_type', 20)->default('fixed'); // fixed, negotiable, contact, free, exchange
            $table->string('currency', 3)->default('RSD'); // BAM, RSD, EUR
            $table->string('condition', 20)->nullable(); // new, used, refurbished
            $table->string('city');
            $table->string('country', 2)->default('RS');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_name')->nullable();
            $table->json('meta')->nullable();
            $table->string('status', 20)->default('active'); // draft, active, expired, sold, deleted
            $table->boolean('is_premium')->default(false);
            $table->timestamp('premium_until')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->unsignedInteger('renewed_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'expires_at']);
            $table->index(['category_id', 'status']);
            $table->index(['country', 'city']);
            $table->index('user_id');
            $table->fullText(['title', 'description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
