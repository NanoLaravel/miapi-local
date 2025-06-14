<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Place extends Model
{
    protected $fillable = [
        'name', 'description', 'address', 'latitude', 'longitude', 'phone', 'website', 'type', 'rating'
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'place_category');
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
