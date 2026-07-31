<?php

namespace App\Helpers;

use App\Models\OwnerSubscription;
use Illuminate\Support\Facades\Auth;

class PlanAccessHelper
{
    public static function getActiveSubscription(int $ownerId): ?OwnerSubscription
    {
        return OwnerSubscription::query()
            ->where('owner_id', $ownerId)
            ->where('status', 'active')
            ->where('payment_status', 'paid')
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->latest('created_at')
            ->first();
    }

    public static function canAccessCommercialFeatures(int $ownerId, string $targetType): bool
    {
        $subscription = self::getActiveSubscription($ownerId);

        if (!$subscription) {
            return false;
        }

        if ($targetType === 'local_product') {
            return true;
        }

        if (in_array($targetType, ['place', 'cabin'], true)) {
            return in_array($subscription->plan?->target_type, ['place', 'hybrid'], true);
        }

        return false;
    }

    public static function canReceiveLeads(int $ownerId, string $leadableType): bool
    {
        if ($leadableType === 'local_product') {
            return self::canAccessCommercialFeatures($ownerId, 'local_product');
        }

        if ($leadableType === 'place') {
            return self::canAccessCommercialFeatures($ownerId, 'place');
        }

        return false;
    }

    public static function canManageReservations(int $ownerId): bool
    {
        return self::canAccessCommercialFeatures($ownerId, 'place');
    }

    public static function canCreatePromotions(int $ownerId): bool
    {
        $subscription = self::getActiveSubscription($ownerId);

        if (!$subscription) {
            return false;
        }

        return (bool) ($subscription->plan?->promotions_enabled || $subscription->plan?->target_type === 'hybrid');
    }

    public static function canAccessAnalytics(int $ownerId): bool
    {
        $subscription = self::getActiveSubscription($ownerId);

        return (bool) ($subscription && $subscription->plan?->analytics_enabled);
    }

    public static function getPlanLimits(int $ownerId, string $targetType): array
    {
        $subscription = self::getActiveSubscription($ownerId);

        if (!$subscription) {
            return [
                'max_images' => $targetType === 'local_product' ? 2 : 5,
                'show_contact_button' => $targetType !== 'cabin',
                'allow_leads' => false,
                'allow_reservations' => false,
                'show_whatsapp_only' => $targetType === 'local_product',
            ];
        }

        return [
            'max_images' => 10,
            'show_contact_button' => true,
            'allow_leads' => self::canReceiveLeads($ownerId, $targetType === 'local_product' ? 'local_product' : 'place'),
            'allow_reservations' => $targetType !== 'local_product' ? self::canManageReservations($ownerId) : false,
            'show_whatsapp_only' => false,
        ];
    }
}
