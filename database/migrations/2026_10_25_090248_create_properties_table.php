<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('office_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('owner_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('governorate_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->string('type', 20);
            $table->string('listing_type', 20);
            $table->string('status', 20)->default('available');

            $table->decimal('price', 15, 2);
            $table->string('currency', 3);

            $table->boolean('is_featured')->default(false);
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['governorate_id', 'listing_type', 'price']);
            $table->index(['status', 'approved_at']);
            $table->index(['is_featured', 'status', 'approved_at']);
            $table->index(['office_id', 'created_at', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
