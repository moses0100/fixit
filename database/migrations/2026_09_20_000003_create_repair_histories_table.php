<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status_from', 20)->nullable();
            $table->string('status_to', 20);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['repair_request_id', 'created_at']);
        });

        DB::table('repair_requests')->orderBy('id')->each(function ($repair): void {
            DB::table('repair_histories')->insert([
                'repair_request_id' => $repair->id,
                'user_id' => null,
                'status_from' => null,
                'status_to' => $repair->status,
                'note' => 'บันทึกสถานะเดิมก่อนเปิดใช้ประวัติการซ่อม',
                'created_at' => $repair->created_at,
                'updated_at' => $repair->updated_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_histories');
    }
};
