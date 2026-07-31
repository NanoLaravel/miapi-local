<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'target_type',
        'price_monthly',
        'price_yearly',
        'lead_limit',
        'featured_limit',
        'analytics_enabled',
        'promotions_enabled',
        'is_active',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'analytics_enabled' => 'boolean',
        'promotions_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(OwnerSubscription::class);
    }
}
