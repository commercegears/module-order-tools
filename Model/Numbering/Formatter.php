<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\Numbering;

/**
 * Renders prefix/suffix placeholders and decides the counter period.
 *
 * Placeholders: {store_id} {yyyy} {yy} {mm} {dd}. Prefix and suffix may contain letters, digits, "-", "_" and "."
 * besides the placeholders, so numbers are safe in URLs, file names, PDFs and CSV files.
 */
class Formatter
{
    public const PLACEHOLDERS = ['{store_id}', '{yyyy}', '{yy}', '{mm}', '{dd}'];

    /** Longest prefix or suffix as typed in the settings (placeholders included). */
    public const MAX_AFFIX_LENGTH = 20;

    /**
     * Whether a prefix or suffix only uses allowed characters and known placeholders.
     */
    public function isValidAffix(string $affix): bool
    {
        if (strlen($affix) > self::MAX_AFFIX_LENGTH) {
            return false;
        }
        $rest = str_replace(self::PLACEHOLDERS, '', $affix);
        return (bool)preg_match('/^[A-Za-z0-9_.\-]*$/', $rest);
    }

    /**
     * Prefix or suffix with all placeholders replaced.
     */
    public function render(string $affix, int $storeId, \DateTimeInterface $date): string
    {
        return strtr($affix, [
            '{store_id}' => (string)$storeId,
            '{yyyy}' => $date->format('Y'),
            '{yy}' => $date->format('y'),
            '{mm}' => $date->format('m'),
            '{dd}' => $date->format('d'),
        ]);
    }

    /**
     * Key of the counter: store views (and settings) with the same prefix and suffix share one counter, so they
     * never hand out the same number. Date placeholders stay in the key; the period handles resets.
     */
    public function counterPattern(Settings $settings): string
    {
        $storeOnly = static fn (string $affix) => str_replace('{store_id}', (string)$settings->storeId, $affix);
        return $storeOnly($settings->prefix) . '|' . $storeOnly($settings->suffix);
    }

    /**
     * Counter period. A reset only happens when the number itself contains the period (year, or year and month),
     * so a restarted counter can never repeat an earlier number.
     */
    public function period(Settings $settings, \DateTimeInterface $date): string
    {
        $affixes = $settings->prefix . $settings->suffix;
        $hasYear = str_contains($affixes, '{yyyy}') || str_contains($affixes, '{yy}');
        if ($settings->reset === Settings::RESET_YEARLY && $hasYear) {
            return 'Y' . $date->format('Y');
        }
        if ($settings->reset === Settings::RESET_MONTHLY && $hasYear && str_contains($affixes, '{mm}')) {
            return 'M' . $date->format('Y-m');
        }
        return '';
    }

    /**
     * Whether the reset setting can take effect with this prefix and suffix.
     */
    public function canReset(string $reset, string $prefix, string $suffix): bool
    {
        $affixes = $prefix . $suffix;
        $hasYear = str_contains($affixes, '{yyyy}') || str_contains($affixes, '{yy}');
        return match ($reset) {
            Settings::RESET_YEARLY => $hasYear,
            Settings::RESET_MONTHLY => $hasYear && str_contains($affixes, '{mm}'),
            default => true,
        };
    }

    public function format(string $prefix, int $value, int $length, string $suffix): string
    {
        return $prefix . str_pad((string)$value, $length, '0', STR_PAD_LEFT) . $suffix;
    }
}
