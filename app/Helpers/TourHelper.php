<?php

namespace App\Helpers;

use App\Models\Tour;
use App\Models\TourRequirement;

/**
 * Tour Helper Functions
 *
 * Provides utility functions for working with tours and tour progression.
 */
class TourHelper
{
    /**
     * Get tour configuration for a specific tour number and type.
     *
     * @param int $tourNumber
     * @param string $type
     * @return array|null
     */
    public static function getTourConfig(int $tourNumber, string $type): ?array
    {
        $requirement = TourRequirement::getRequirement($tourNumber, $type);

        if (!$requirement) {
            return null;
        }

        return [
            'tour_number' => $tourNumber,
            'type' => $type,
            'amount_to_pay' => $requirement->amount_to_pay,
            'amount_to_receive' => $requirement->amount_to_receive,
            'amount_to_keep' => $requirement->amount_to_keep,
            'required_members' => $requirement->required_members,
        ];
    }

    /**
     * Get status label for a tour.
     *
     * @param Tour|string $tour
     * @return string
     */
    public static function getStatus($tour): string
    {
        $status = is_string($tour) ? $tour : $tour->status;

        return match($status) {
            'pending' => 'En attente',
            'active' => 'Actif',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            default => 'Inconnu',
        };
    }

    /**
     * Get tour name with emoji.
     *
     * @param int $tourNumber
     * @return string
     */
    public static function getTourName(int $tourNumber): string
    {
        $emojis = [
            1 => '🌟',
            2 => '✨',
            3 => '💫',
            4 => '⭐',
            5 => '🌠',
            6 => '💥',
            7 => '🏆',
        ];

        $emoji = $emojis[$tourNumber] ?? '🎯';
        return "{$emoji} Tour {$tourNumber}";
    }

    /**
     * Calculate tour progress percentage.
     *
     * @param Tour $tour
     * @return int
     */
    public static function calculateProgress(Tour $tour): int
    {
        if ($tour->required_members == 0) {
            return 0;
        }

        return (int) round(($tour->members_paid / $tour->required_members) * 100);
    }

    /**
     * Get next tour number.
     *
     * @param int $currentTour
     * @return int|null
     */
    public static function getNextTour(int $currentTour): ?int
    {
        return $currentTour < 7 ? $currentTour + 1 : null;
    }

    /**
     * Check if tour is final tour.
     *
     * @param int $tourNumber
     * @return bool
     */
    public static function isFinalTour(int $tourNumber): bool
    {
        return $tourNumber === 7;
    }

    /**
     * Get total earnings for completing all tours.
     *
     * @param string $type
     * @return float
     */
    public static function getTotalEarnings(string $type): float
    {
        return TourRequirement::getTotalEarnings($type);
    }

    /**
     * Get all tour amounts for a constellation type.
     *
     * @param string $type
     * @return array
     */
    public static function getAllTourAmounts(string $type): array
    {
        $requirements = TourRequirement::getAllForType($type);
        $tours = [];

        foreach ($requirements as $req) {
            $tours[$req->tour_number] = [
                'offered' => $req->amount_to_pay,
                'received' => $req->amount_to_receive,
                'kept' => $req->amount_to_keep,
            ];
        }

        return $tours;
    }

    /**
     * Format tour status as badge HTML.
     *
     * @param string $status
     * @return string
     */
    public static function statusBadge(string $status): string
    {
        $colors = [
            'pending' => 'gray',
            'active' => 'blue',
            'in_progress' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            'cancelled' => 'orange',
        ];

        $color = $colors[$status] ?? 'gray';
        $label = self::getStatus($status);

        return "<span class=\"badge badge-{$color}\">{$label}</span>";
    }

    /**
     * Get tour icon based on status.
     *
     * @param string $status
     * @return string
     */
    public static function getIcon(string $status): string
    {
        return match($status) {
            'pending' => '⏳',
            'active' => '🎯',
            'in_progress' => '🔄',
            'completed' => '✅',
            'failed' => '❌',
            'cancelled' => '🚫',
            default => '❓',
        };
    }

    /**
     * Check if user can start a specific tour.
     *
     * @param int $tourNumber
     * @param int $userCurrentTour
     * @return bool
     */
    public static function canStartTour(int $tourNumber, int $userCurrentTour): bool
    {
        return $tourNumber === $userCurrentTour;
    }

    /**
     * Get tour multiplier (how much you receive vs what you pay).
     *
     * @param int $tourNumber
     * @param string $type
     * @return float
     */
    public static function getMultiplier(int $tourNumber, string $type): float
    {
        $requirement = TourRequirement::getRequirement($tourNumber, $type);

        if (!$requirement || $requirement->amount_to_pay == 0) {
            return 0;
        }

        return round($requirement->amount_to_receive / $requirement->amount_to_pay, 2);
    }
}
