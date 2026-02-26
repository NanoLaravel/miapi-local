<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class Advertisement extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'link_url',
        'type',
        'position',
        'start_date',
        'end_date',
        'is_active',
        'priority',
        'clicks_count',
        'views_count',
        'place_id',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'clicks_count' => 'integer',
        'views_count' => 'integer',
        'priority' => 'integer',
    ];

    /**
     * Tipos de anuncio disponibles
     */
    public const TYPE_BANNER = 'banner';
    public const TYPE_POPUP = 'popup';
    public const TYPE_SIDEBAR = 'sidebar';
    public const TYPE_INLINE = 'inline';

    /**
     * Posiciones disponibles
     */
    public const POSITION_HOME = 'home';
    public const POSITION_PLACES = 'places';
    public const POSITION_EVENTS = 'events';
    public const POSITION_ALL = 'all';

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
     * Scope para anuncios activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Scope para filtrar por posición
     */
    public function scopeByPosition(Builder $query, string $position): Builder
    {
        return $query->where(function ($q) use ($position) {
            $q->where('position', $position)
              ->orWhere('position', self::POSITION_ALL);
        });
    }

    /**
     * Scope para ordenar por prioridad
     */
    public function scopeOrderedByPriority(Builder $query): Builder
    {
        return $query->orderByDesc('priority');
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Incrementar contador de vistas
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Incrementar contador de clics
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    /**
     * Verificar si el anuncio está vigente
     */
    public function isValid(): bool
    {
        return $this->is_active 
               && $this->start_date <= now() 
               && $this->end_date >= now();
    }

    /**
     * Obtener tipos disponibles
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_BANNER => 'Banner',
            self::TYPE_POPUP => 'Popup',
            self::TYPE_SIDEBAR => 'Sidebar',
            self::TYPE_INLINE => 'En línea',
        ];
    }

    /**
     * Obtener posiciones disponibles
     */
    public static function getPositions(): array
    {
        return [
            self::POSITION_HOME => 'Inicio',
            self::POSITION_PLACES => 'Lugares',
            self::POSITION_EVENTS => 'Eventos',
            self::POSITION_ALL => 'Todas las secciones',
        ];
    }
}
