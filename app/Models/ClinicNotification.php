<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicNotification extends Model
{
    protected $table = 'clinic_notifications';

    protected $fillable = ['user_id', 'type', 'title', 'message', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    /** type: appointment_reminder | record_updated | system */
    public static function send(int $userId, string $type, string $title, string $message): self
    {
        return static::create(['user_id' => $userId, 'type' => $type, 'title' => $title, 'message' => $message]);
    }
}
