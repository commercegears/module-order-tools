<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\Numbering;

use Softaware\OrderTools\Model\Config;

/**
 * Numbering settings of one document type in one store view.
 */
class Settings
{
    public const RESET_NEVER = 'never';
    public const RESET_YEARLY = 'yearly';
    public const RESET_MONTHLY = 'monthly';

    /** Longest allowed zero padding of the counter. */
    public const MAX_LENGTH = 10;

    public function __construct(
        public readonly string $entityType,
        public readonly int $storeId,
        public readonly string $mode,
        public readonly string $prefix,
        public readonly string $suffix,
        public readonly int $length,
        public readonly int $start,
        public readonly int $step,
        public readonly string $reset
    ) {
    }

    public function isCustom(): bool
    {
        return $this->mode === Config::MODE_CUSTOM;
    }

    public function isSameAsOrder(): bool
    {
        return $this->mode === Config::MODE_SAME_AS_ORDER;
    }
}
