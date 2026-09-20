<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'device_type', 'brand', 'model', 'serial_number'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repairRequests(): HasMany
    {
        return $this->hasMany(RepairRequest::class)->latest('created_at')->latest('id');
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->brand.' '.($this->model ?: $this->device_type));
    }

    public static function forRepair(User $user, array $data): self
    {
        $query = static::query()
            ->where('user_id', $user->id)
            ->where('device_type', $data['device_type'])
            ->where('brand', $data['brand'])
            ->where('model', $data['model'] ?? null);

        $serial = $data['serial_number'] ?? null;
        $query = $serial ? $query->where('serial_number', $serial) : $query->whereNull('serial_number');
        $device = $query->first();

        if ($device) {
            return $device;
        }

        return static::create([
            'user_id' => $user->id,
            'device_type' => $data['device_type'],
            'brand' => $data['brand'],
            'model' => $data['model'] ?? null,
            'serial_number' => $serial,
        ]);
    }
}
