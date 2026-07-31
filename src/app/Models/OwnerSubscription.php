<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerSubscription extends Model
{
    protected $fillable = [
        'owner_id',
        'subscription_plan_id',
        'status',
        'started_at',
        'ends_at',
        'auto_renew',
        'payment_status',
    ];

    protected $casts = [
        'auto_renew' => 'boolean',
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
