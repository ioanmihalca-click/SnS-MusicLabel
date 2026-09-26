<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demos sent through the form at /demos, reviewed in the admin
 * (App\Filament\Resources\DemoSubmissionResource).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('demo_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('artist_name');
            $table->string('email');
            $table->string('link', 2048);
            $table->string('genre')->nullable();
            $table->string('country')->nullable();
            $table->text('message')->nullable();
            $table->boolean('rights_confirmed')->default(false);
            $table->string('status')->default('new')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo_submissions');
    }
};
