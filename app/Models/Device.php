<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'group_id', 'name', 'brand', 'watts', 'volts', 'amps',
        'always_plugged', 'standby_watts', 'notes',
    ];

    protected $casts = [
        'watts' => 'float',
        'volts' => 'float',
        'amps' => 'float',
        'standby_watts' => 'float',
        'always_plugged' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function usageLogs(): HasMany
    {
        return $this->hasMany(UsageLog::class);
    }
}
