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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            // Matches the old site's /post/{category}/{slug}, so the URLs that
            // are already indexed keep working after the cutover.
            $table->string('category')->index();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('cover_image')->nullable();
            // Null where the source article carried no dateline. 26 of the 61
            // imported articles are in that state and need a human to confirm
            // them. Deliberately not guessed at import time.
            $table->timestamp('published_at')->nullable()->index();
            $table->string('seo_title')->nullable();
            // Kept for auditing the migration and for building redirects.
            $table->string('source_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
