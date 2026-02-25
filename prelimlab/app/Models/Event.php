<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'capacity',
        'attendees_count'
    ];

    public function isFull()
    {
        return $this->attendees_count >= $this->capacity;
    }

    public function remainingSlots()
    {
        return $this->capacity - $this->attendees_count;
    }
}