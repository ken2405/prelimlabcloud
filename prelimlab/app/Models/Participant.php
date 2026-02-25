<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    // These fields must be fillable so $event->participants()->create(...) works
    protected $fillable = [
        'event_id',
        'name',
        'email',
    ];

    /**
     * Inverse relationship: A participant belongs to a specific event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
