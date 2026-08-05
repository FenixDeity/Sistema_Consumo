<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends Model
{
    protected $fillable = [
        'device_id', 'user_id', 'log_date', 'mode',
        'minutes', 'cycles', 'cycle_minutes', 'kwh', 'note',
    ];

    protected $casts = [
        'log_date' => 'date',
        'minutes' => 'integer',
        'cycles' => 'integer',
        'cycle_minutes' => 'integer',
        'kwh' => 'float',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Total de horas de uso del registro. */
    public function hours(): float
    {
        if ($this->mode === 'cycles') {
            return (($this->cycles ?? 0) * ($this->cycle_minutes ?? 0)) / 60;
        }

        return ($this->minutes ?? 0) / 60;
    }
}
