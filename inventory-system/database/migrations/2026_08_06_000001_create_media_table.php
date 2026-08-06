<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stores uploaded file bytes in the database instead of on disk.
 *
 * Serverless hosts give each request a fresh, read-only filesystem, so a file
 * written to storage/ during an upload is gone by the time it is requested
 * back. Keeping the bytes in MySQL makes uploads survive.
 *
 * The bytes live in their own table rather than as a column on products /
 * inventory_items so that listing pages never pull image data into a query
 * they don't need it for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable');
            $table->string('collection')->default('image');
            $table->string('mime')->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->binary('data');
            $table->timestamps();

            $table->unique(['mediable_type', 'mediable_id', 'collection']);
        });

        // Laravel's binary() maps to MySQL BLOB, which caps at 64 KB — far too
        // small for a photo. LONGBLOB has no practical limit here.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE media MODIFY data LONGBLOB NOT NULL');
        }

        // A tiny marker on the owning row, so building an image URL never has
        // to query the media table (which would mean an N+1 on index pages).
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_mime')->nullable()->after('image_path');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('image_mime')->nullable()->after('image_path');
        });

        // Proofs stored in the database have no path. Existing rows keep theirs
        // and are still served from disk.
        Schema::table('project_proofs', function (Blueprint $table) {
            $table->string('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', fn (Blueprint $table) => $table->dropColumn('image_mime'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('image_mime'));
        Schema::dropIfExists('media');
    }
};
