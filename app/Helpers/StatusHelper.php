<?php

namespace App\Helpers;

/**
 * Status Helper Functions
 *
 * Provides utility functions for working with statuses across the application.
 */
class StatusHelper
{
    /**
     * Get French label for user status.
     *
     * @param string $status
     * @return string
     */
    public static function getUserStatus(string $status): string
    {
        return match($status) {
            'active' => 'Actif',
            'suspended' => 'Suspendu',
            'banned' => 'Banni',
            'pending_verification' => 'En attente de vérification',
            default => 'Inconnu',
        };
    }

    /**
     * Get French label for constellation status.
     *
     * @param string $status
     * @return string
     */
    public static function getConstellationStatus(string $status): string
    {
        return match($status) {
            'forming' => 'En formation',
            'active' => 'Active',
            'completed' => 'Terminée',
            'disbanded' => 'Dissoute',
            'frozen' => 'Gelée',
            default => 'Inconnu',
        };
    }

    /**
     * Get French label for transaction status.
     *
     * @param string $status
     * @return string
     */
    public static function getTransactionStatus(string $status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'processing' => 'En cours',
            'completed' => 'Terminée',
            'failed' => 'Échouée',
            'refunded' => 'Remboursée',
            'cancelled' => 'Annulée',
            'on_hold' => 'En suspens',
            default => 'Inconnu',
        };
    }

    /**
     * Get French label for payout status.
     *
     * @param string $status
     * @return string
     */
    public static function getPayoutStatus(string $status): string
    {
        return match($status) {
            'pending' => 'En attente',
            'processing' => 'En traitement',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            'rejected' => 'Rejeté',
            'cancelled' => 'Annulé',
            default => 'Inconnu',
        };
    }

    /**
     * Get badge HTML for any status.
     *
     * @param string $status
     * @param string $type (user, constellation, transaction, payout, tour)
     * @return string
     */
    public static function badge(string $status, string $type = 'generic'): string
    {
        $color = self::getColor($status);
        $label = self::getLabel($status, $type);

        return "<span class=\"inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{$color}-100 text-{$color}-800\">{$label}</span>";
    }

    /**
     * Get status color.
     *
     * @param string $status
     * @return string
     */
    public static function getColor(string $status): string
    {
        return match($status) {
            'active', 'completed', 'paid' => 'green',
            'pending', 'forming', 'waiting' => 'yellow',
            'processing', 'in_progress', 'partial' => 'blue',
            'failed', 'banned', 'rejected' => 'red',
            'cancelled', 'suspended', 'disbanded', 'frozen' => 'gray',
            'refunded', 'on_hold' => 'orange',
            'qualified' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get status label based on type.
     *
     * @param string $status
     * @param string $type
     * @return string
     */
    public static function getLabel(string $status, string $type = 'generic'): string
    {
        return match($type) {
            'user' => self::getUserStatus($status),
            'constellation' => self::getConstellationStatus($status),
            'transaction' => self::getTransactionStatus($status),
            'payout' => self::getPayoutStatus($status),
            'tour' => TourHelper::getStatus($status),
            default => ucfirst($status),
        };
    }

    /**
     * Get status icon.
     *
     * @param string $status
     * @return string
     */
    public static function getIcon(string $status): string
    {
        return match($status) {
            'active' => '✅',
            'completed', 'paid' => '🎉',
            'pending', 'waiting' => '⏳',
            'processing', 'in_progress' => '🔄',
            'failed' => '❌',
            'cancelled' => '🚫',
            'refunded' => '💰',
            'suspended', 'frozen' => '❄️',
            'banned', 'disbanded' => '🔴',
            'qualified' => '✔️',
            default => '⚪',
        };
    }

    /**
     * Check if status is a success state.
     *
     * @param string $status
     * @return bool
     */
    public static function isSuccess(string $status): bool
    {
        return in_array($status, ['active', 'completed', 'paid']);
    }

    /**
     * Check if status is a pending state.
     *
     * @param string $status
     * @return bool
     */
    public static function isPending(string $status): bool
    {
        return in_array($status, ['pending', 'waiting', 'forming']);
    }

    /**
     * Check if status is a failure state.
     *
     * @param string $status
     * @return bool
     */
    public static function isFailure(string $status): bool
    {
        return in_array($status, ['failed', 'banned', 'rejected', 'cancelled']);
    }

    /**
     * Get all possible statuses for a type.
     *
     * @param string $type
     * @return array
     */
    public static function getAllStatuses(string $type): array
    {
        return match($type) {
            'user' => ['active', 'suspended', 'banned', 'pending_verification'],
            'constellation' => ['forming', 'active', 'completed', 'disbanded', 'frozen'],
            'transaction' => ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled', 'on_hold'],
            'payout' => ['pending', 'processing', 'completed', 'failed', 'rejected', 'cancelled'],
            'tour' => ['pending', 'active', 'in_progress', 'completed', 'failed', 'cancelled'],
            'payment' => ['pending', 'partial', 'paid', 'overdue', 'cancelled'],
            'referral' => ['pending', 'qualified', 'paid', 'expired', 'cancelled'],
            default => [],
        };
    }
}
