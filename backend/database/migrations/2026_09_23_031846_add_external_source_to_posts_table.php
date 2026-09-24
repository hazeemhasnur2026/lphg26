<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a post be a pointer to coverage published elsewhere rather than an
 * article we host.
 *
 * Press coverage belongs to the outlet that wrote it. Recording the headline,
 * outlet, date and link is citation. Reproducing the text on our own domain
 * would need a licence from the publisher. ANTARA, for one, states plainly on
 * every article page that automated harvesting of its content is prohibited
 * without written permission.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Set => this record links out and has no detail page of its own.
            $table->string('external_url')->nullable()->after('source_url');
            $table->string('source_name')->nullable()->after('external_url');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['external_url', 'source_name']);
        });
    }
};
