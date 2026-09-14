<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('device_type', 50);
            $table->string('brand', 100);
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('title', 150);
            $table->text('problem_description');
            $table->string('urgency', 20)->default('medium')->index();
            $table->string('contact_phone', 30);
            $table->string('status', 20)->default('pending')->index();
            $table->string('image')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_requests');
    }
};
