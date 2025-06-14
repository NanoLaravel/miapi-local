<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = ['name', 'description'];

    public function places(): BelongsToMany
    {
        return $this->belongsToMany(Place::class, 'place_category');
    }
}
