<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $fillable = ['name', 'code', 'owner_id'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_members')->withTimestamps();
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = strtoupper(Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
