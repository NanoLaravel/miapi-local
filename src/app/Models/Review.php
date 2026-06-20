<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'place_id', 'user_id', 'rating', 'comment',
        'cleanliness', 'accuracy', 'check_in', 'communication', 'location', 'price'
    ];

    /**
     * Calcula el promedio de los ítems calificados (excluyendo nulls).
     */
    public function getItemsAverageAttribute()
    {
        $items = [
            $this->cleanliness, $this->accuracy, $this->check_in,
            $this->communication, $this->location, $this->price
        ];
        $scored = array_filter($items, fn($v) => $v !== null);
        return count($scored) ? round(array_sum($scored) / count($scored), 2) : null;
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
