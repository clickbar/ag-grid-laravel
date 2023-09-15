<?php

namespace Clickbar\AgGrid\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zoo extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'immutable_datetime',
        'updated_at' => 'immutable_datetime',
        'address' => 'array',
    ];

    public function keepers(): HasMany
    {
        return $this->hasMany(Keeper::class);
    }
}
