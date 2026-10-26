<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('images', 'path')) {
            Schema::table('images', function (Blueprint $table) {
                $table->string('path')->nullable();
            });
        }

        if (! Schema::hasColumn('images', 'sort_order')) {
            Schema::table('images', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0);
            });
        }

        if (
            ! Schema::hasColumn('images', 'imageable_type') ||
            ! Schema::hasColumn('images', 'imageable_id')
        ) {
            Schema::table('images', function (Blueprint $table) {
                $table->nullableMorphs('imageable');
            });
        }
    }

    public function down(): void
    {
        // لا نحذف الأعمدة هنا حتى لا نفقد بيانات موجودة.
    }
};
