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
        Schema::create('citizen_cases', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('NEW');
            $table->string('applicant_name')->nullable();
            $table->string('applicant_national_id')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizen_cases');
    }
};
