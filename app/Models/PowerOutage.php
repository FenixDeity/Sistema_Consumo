<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PowerOutage extends Model
{
    protected $fillable = ['user_id', 'group_id', 'started_at', 'ended_at', 'minutes', 'note'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
