<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class LocalProduct extends Model
{
    protected $fillable = [
        'name',
        'price',
        'description',
        'producer_name',
        'approximate_location',
        'phone',
        'facebook_url',
        'instagram_url',
        'is_active',
        'is_featured',
        'user_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected $appends = ['whatsapp_url', 'first_image_url'];

    /**
     * Relación con el usuario creador (admin/editor)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica con imágenes
     */
    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Accessor para obtener la primera imagen de la galería polimórfica
     */
    public function getFirstImageUrlAttribute(): ?string
    {
        $firstImage = $this->images()->first();
        return $firstImage ? $firstImage->path : null;
    }

    /**
     * Accessor para generar el enlace directo a WhatsApp
     */
    public function getWhatsappUrlAttribute(): string
    {
        // Limpiar el teléfono para wa.me (dejar solo dígitos)
        $cleanPhone = preg_replace('/[^0-9]/', '', $this->phone);
        
        // Si no tiene el prefijo de Colombia (57) y tiene 10 dígitos, agregarlo
        if (strlen($cleanPhone) === 10 && !str_starts_with($cleanPhone, '57')) {
            $cleanPhone = '57' . $cleanPhone;
        }
        
        $message = "Hola, vi tu producto '{$this->name}' en la app y me gustaría obtener más información.";
        return "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
    }

    /**
     * Scope para productos activos
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para productos destacados
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
