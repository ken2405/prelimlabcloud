<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Participant;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'category',
        'capacity',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
}