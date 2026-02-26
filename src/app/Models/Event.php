<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'latitude',
        'longitude',
        'image_path',
        'is_active',
        'is_featured',
        'price',
        'contact_phone',
        'contact_email',
        'website',
        'place_id',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Relación con el lugar asociado (opcional)
     */
    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }

    /**
     * Relación con el usuario creador
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica con imágenes (múltiples imágenes)
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Obtener la primera imagen o la imagen principal
     */
    public function getFirstImageUrlAttribute(): ?string
    {
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->path;
        }
        return $this->image_path;
    }

    /**
     * Scope para eventos activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para eventos destacados
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope para eventos actuales (en curso o próximos)
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('end_date', '>=', now())
                     ->where('is_active', true)
                     ->orderBy('start_date');
    }

    /**
     * Scope para eventos próximos
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now())
                     ->where('is_active', true)
                     ->orderBy('start_date');
    }

    /**
     * Scope para eventos en curso
     */
    public function scopeOngoing(Builder $query): Builder
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now())
                     ->where('is_active', true);
    }

    /**
     * Verificar si el evento está en curso
     */
    public function isOngoing(): bool
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    /**
     * Verificar si el evento ya terminó
     */
    public function hasEnded(): bool
    {
        return $this->end_date < now();
    }

    /**
     * Verificar si el evento es próximo
     */
    public function isUpcoming(): bool
    {
        return $this->start_date > now();
    }
}
