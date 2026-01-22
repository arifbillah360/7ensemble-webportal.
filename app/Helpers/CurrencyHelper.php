<?php

namespace App\Helpers;

/**
 * Currency Helper Functions
 *
 * Provides utility functions for working with currencies and amounts.
 */
class CurrencyHelper
{
    /**
     * Format amount in EUR.
     *
     * @param float|int $amount
     * @param bool $showSymbol
     * @param string $locale
     * @return string
     */
    public static function formatEur($amount, bool $showSymbol = true, string $locale = 'fr_FR'): string
    {
        $formatted = number_format((float) $amount, 2, ',', ' ');

        return $showSymbol ? $formatted . ' €' : $formatted;
    }

    /**
     * Format amount in any currency.
     *
     * @param float|int $amount
     * @param string $currency
     * @param bool $showSymbol
     * @return string
     */
    public static function format($amount, string $currency = 'EUR', bool $showSymbol = true): string
    {
        $symbols = [
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'CHF' => 'CHF',
            'BTC' => '₿',
            'ETH' => 'Ξ',
        ];

        $formatted = number_format((float) $amount, 2, ',', ' ');
        $symbol = $symbols[$currency] ?? $currency;

        if (!$showSymbol) {
            return $formatted;
        }

        // For some currencies, symbol goes before
        if (in_array($currency, ['USD', 'GBP'])) {
            return $symbol . ' ' . $formatted;
        }

        return $formatted . ' ' . $symbol;
    }

    /**
     * Parse formatted amount to float.
     *
     * @param string $formattedAmount
     * @return float
     */
    public static function parse(string $formattedAmount): float
    {
        // Remove spaces, currency symbols, and replace comma with dot
        $cleaned = str_replace([' ', '€', '$', '£', 'CHF', ','], ['', '', '', '', '', '.'], $formattedAmount);

        return (float) $cleaned;
    }

    /**
     * Get currency symbol.
     *
     * @param string $currency
     * @return string
     */
    public static function getSymbol(string $currency): string
    {
        return match($currency) {
            'EUR' => '€',
            'USD' => '$',
            'GBP' => '£',
            'CHF' => 'CHF',
            'BTC' => '₿',
            'ETH' => 'Ξ',
            default => $currency,
        };
    }

    /**
     * Convert cents to euros.
     *
     * @param int $cents
     * @return float
     */
    public static function centsToEuros(int $cents): float
    {
        return $cents / 100;
    }

    /**
     * Convert euros to cents.
     *
     * @param float $euros
     * @return int
     */
    public static function eurosToCents(float $euros): int
    {
        return (int) round($euros * 100);
    }

    /**
     * Calculate percentage of amount.
     *
     * @param float $amount
     * @param float $percentage
     * @return float
     */
    public static function calculatePercentage(float $amount, float $percentage): float
    {
        return round(($amount * $percentage) / 100, 2);
    }

    /**
     * Calculate amount after deducting percentage.
     *
     * @param float $amount
     * @param float $percentage
     * @return float
     */
    public static function afterPercentageDeduction(float $amount, float $percentage): float
    {
        $deduction = self::calculatePercentage($amount, $percentage);
        return round($amount - $deduction, 2);
    }

    /**
     * Calculate percentage that one amount represents of another.
     *
     * @param float $part
     * @param float $total
     * @return float
     */
    public static function getPercentage(float $part, float $total): float
    {
        if ($total == 0) {
            return 0;
        }

        return round(($part / $total) * 100, 2);
    }

    /**
     * Format large numbers with K, M, B suffixes.
     *
     * @param float $amount
     * @param int $decimals
     * @return string
     */
    public static function formatCompact(float $amount, int $decimals = 1): string
    {
        if ($amount >= 1000000000) {
            return number_format($amount / 1000000000, $decimals) . 'B €';
        } elseif ($amount >= 1000000) {
            return number_format($amount / 1000000, $decimals) . 'M €';
        } elseif ($amount >= 1000) {
            return number_format($amount / 1000, $decimals) . 'K €';
        }

        return self::formatEur($amount);
    }

    /**
     * Format amount with color based on positive/negative.
     *
     * @param float $amount
     * @param bool $invertColors
     * @return string
     */
    public static function formatWithColor(float $amount, bool $invertColors = false): string
    {
        $isPositive = $amount >= 0;
        $color = $invertColors
            ? ($isPositive ? 'red' : 'green')
            : ($isPositive ? 'green' : 'red');

        $formatted = self::formatEur(abs($amount));
        $sign = $amount >= 0 ? '+' : '-';

        return "<span class=\"text-{$color}-600 font-semibold\">{$sign} {$formatted}</span>";
    }

    /**
     * Calculate ROI (Return on Investment).
     *
     * @param float $invested
     * @param float $returned
     * @return float Percentage
     */
    public static function calculateROI(float $invested, float $returned): float
    {
        if ($invested == 0) {
            return 0;
        }

        return round((($returned - $invested) / $invested) * 100, 2);
    }

    /**
     * Format amount as accounting notation (negative in parentheses).
     *
     * @param float $amount
     * @return string
     */
    public static function formatAccounting(float $amount): string
    {
        if ($amount < 0) {
            return '(' . self::formatEur(abs($amount)) . ')';
        }

        return self::formatEur($amount);
    }

    /**
     * Add amounts safely (avoiding floating point issues).
     *
     * @param float ...$amounts
     * @return float
     */
    public static function add(float ...$amounts): float
    {
        $total = 0;
        foreach ($amounts as $amount) {
            $total += $amount;
        }

        return round($total, 2);
    }

    /**
     * Subtract amounts safely (avoiding floating point issues).
     *
     * @param float $from
     * @param float ...$amounts
     * @return float
     */
    public static function subtract(float $from, float ...$amounts): float
    {
        $result = $from;
        foreach ($amounts as $amount) {
            $result -= $amount;
        }

        return round($result, 2);
    }

    /**
     * Multiply amount safely.
     *
     * @param float $amount
     * @param float $multiplier
     * @return float
     */
    public static function multiply(float $amount, float $multiplier): float
    {
        return round($amount * $multiplier, 2);
    }

    /**
     * Divide amount safely.
     *
     * @param float $amount
     * @param float $divisor
     * @return float
     */
    public static function divide(float $amount, float $divisor): float
    {
        if ($divisor == 0) {
            return 0;
        }

        return round($amount / $divisor, 2);
    }

    /**
     * Check if amount is valid (positive or zero).
     *
     * @param float $amount
     * @return bool
     */
    public static function isValid(float $amount): bool
    {
        return $amount >= 0;
    }

    /**
     * Format amount for Stripe (in cents).
     *
     * @param float $amount
     * @return int
     */
    public static function toStripeCents(float $amount): int
    {
        return self::eurosToCents($amount);
    }

    /**
     * Format amount from Stripe (from cents).
     *
     * @param int $cents
     * @return float
     */
    public static function fromStripeCents(int $cents): float
    {
        return self::centsToEuros($cents);
    }
}
