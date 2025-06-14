<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    protected $fillable = ['place_id', 'path', 'description'];

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
