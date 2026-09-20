<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
        });

        $deviceIds = [];
        DB::table('repair_requests')->orderBy('id')->chunkById(100, function ($repairs) use (&$deviceIds): void {
            foreach ($repairs as $repair) {
                $serial = $repair->serial_number ?: null;
                $key = implode('|', [$repair->user_id, $serial ?? '', $repair->device_type, $repair->brand, $repair->model ?? '']);

                if (! isset($deviceIds[$key])) {
                    $query = DB::table('devices')
                        ->where('user_id', $repair->user_id)
                        ->where('device_type', $repair->device_type)
                        ->where('brand', $repair->brand)
                        ->where('model', $repair->model);

                    $query = $serial ? $query->where('serial_number', $serial) : $query->whereNull('serial_number');
                    $device = $query->first();

                    $deviceIds[$key] = $device?->id ?? DB::table('devices')->insertGetId([
                        'user_id' => $repair->user_id,
                        'device_type' => $repair->device_type,
                        'brand' => $repair->brand,
                        'model' => $repair->model,
                        'serial_number' => $serial,
                        'created_at' => $repair->created_at,
                        'updated_at' => $repair->updated_at,
                    ]);
                }

                DB::table('repair_requests')->where('id', $repair->id)->update(['device_id' => $deviceIds[$key]]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('device_id');
        });
    }
};
