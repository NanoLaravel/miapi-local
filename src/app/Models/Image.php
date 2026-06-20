<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    protected $fillable = ['imageable_type', 'imageable_id', 'path', 'description'];

    /**
     * Relación polimórfica inversa
     * La imagen puede pertenecer a un Place, Event, Advertisement, etc.
     */
    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relación con Place (para compatibilidad hacia atrás)
     * @deprecated Use imageable() instead
     */
     public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'imageable_id');
    } 
}
