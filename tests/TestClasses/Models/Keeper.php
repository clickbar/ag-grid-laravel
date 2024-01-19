<?php

namespace Clickbar\AgGrid\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keeper extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
    ];

    public function flamingos(): HasMany
    {
        return $this->hasMany(Flamingo::class);
    }

    public function zoo(): BelongsTo
    {
        return $this->belongsTo(Zoo::class);
    }
}
