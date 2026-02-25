<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'category',
        'capacity',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function getRemainingCapacityAttribute()
    {
        return $this->capacity - $this->participants()->count();
    }

    public function isEventFull(): bool
    {
        return $this->participants()->count() >= $this->capacity;
    }
}
