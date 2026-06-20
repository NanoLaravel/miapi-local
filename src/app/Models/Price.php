<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends Model
{
    protected $fillable = [
        'place_id', 'type', 'value', 'currency', 'description'
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
