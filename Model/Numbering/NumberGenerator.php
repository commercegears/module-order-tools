<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\Numbering;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Builds custom document numbers.
 *
 * Uniqueness: the counter never hands out a value twice; on top of that every candidate number is checked against
 * the document table (all store views) and skipped if it already exists (e.g. after the prefix was changed back to an
 * older one). The unique key on increment_id + store_id in Magento's tables stays the final guard.
 */
class NumberGenerator
{
    /** Document tables by sequence entity type. */
    public const TABLES = [
        'order' => 'sales_order',
        'invoice' => 'sales_invoice',
        'shipment' => 'sales_shipment',
        'creditmemo' => 'sales_creditmemo',
    ];

    /** Longest number Magento's increment_id columns can store. */
    public const MAX_NUMBER_LENGTH = 50;

    private const MAX_ATTEMPTS = 100;

    public function __construct(
        private readonly Counter $counter,
        private readonly Formatter $formatter,
        private readonly ResourceConnection $resource,
        private readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * Takes the next custom number.
     *
     * @throws LocalizedException when no free number could be built (the caller falls back to Magento's number)
     */
    public function next(Settings $settings): string
    {
        $this->assertUsable($settings);
        $date = $this->timezone->scopeDate($settings->storeId, null, true);
        $prefix = $this->formatter->render($settings->prefix, $settings->storeId, $date);
        $suffix = $this->formatter->render($settings->suffix, $settings->storeId, $date);
        $pattern = $this->formatter->counterPattern($settings);
        $period = $this->formatter->period($settings, $date);

        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $value = $this->counter->next($settings->entityType, $pattern, $period, $settings->start, $settings->step);
            $number = $this->formatter->format($prefix, $value, $settings->length, $suffix);
            if (strlen($number) > self::MAX_NUMBER_LENGTH) {
                throw new LocalizedException(__('The document number "%1" is too long.', $number));
            }
            if (!$this->exists($settings->entityType, $number)) {
                return $number;
            }
        }
        throw new LocalizedException(
            __('No free %1 number found after %2 attempts.', $settings->entityType, self::MAX_ATTEMPTS)
        );
    }

    /**
     * The number next() would most likely return now (without taking it). Null for Magento's own numbering.
     */
    public function preview(Settings $settings): ?string
    {
        if (!$settings->isCustom()) {
            return null;
        }
        $date = $this->timezone->scopeDate($settings->storeId, null, true);
        $value = $this->counter->peek(
            $settings->entityType,
            $this->formatter->counterPattern($settings),
            $this->formatter->period($settings, $date),
            $settings->start,
            $settings->step
        );
        return $this->formatter->format(
            $this->formatter->render($settings->prefix, $settings->storeId, $date),
            $value,
            $settings->length,
            $this->formatter->render($settings->suffix, $settings->storeId, $date)
        );
    }

    /**
     * Invoice/shipment/credit memo number equal to the order number; "-1", "-2", ... for further documents of the
     * same order. Null if no free number was found (Magento's numbering is used then).
     */
    public function sameAsOrder(string $entityType, string $orderNumber): ?string
    {
        if ($orderNumber === '' || !isset(self::TABLES[$entityType])) {
            return null;
        }
        for ($n = 0; $n < self::MAX_ATTEMPTS; $n++) {
            $number = $n === 0 ? $orderNumber : $orderNumber . '-' . $n;
            if (strlen($number) > self::MAX_NUMBER_LENGTH) {
                return null;
            }
            if (!$this->exists($entityType, $number)) {
                return $number;
            }
        }
        return null;
    }

    /**
     * Last number of this document type in the store view (newest document), or null.
     */
    /**
     * The number Magento's own sequence gives the next document of this type and store view (read-only: nothing
     * is reserved). Null when the store view has no sequence or it cannot be read.
     */
    public function magentoNextNumber(string $entityType, int $storeId): ?string
    {
        try {
            $connection = $this->resource->getConnection('sales');
            $row = $connection->fetchRow(
                $connection->select()
                    ->from(['m' => $this->resource->getTableName('sales_sequence_meta')], ['sequence_table'])
                    ->join(
                        ['p' => $this->resource->getTableName('sales_sequence_profile')],
                        'p.meta_id = m.meta_id AND p.is_active = 1',
                        ['prefix', 'suffix', 'start_value', 'step']
                    )
                    ->where('m.entity_type = ?', $entityType)
                    ->where('m.store_id = ?', $storeId)
                    ->limit(1)
            );
            if (!$row || !preg_match('/^[a-z0-9_]+$/i', (string)$row['sequence_table'])) {
                return null;
            }
            $table = $this->resource->getTableName((string)$row['sequence_table']);
            // The next value is the table's auto-increment counter (MAX + 1 when the counter cannot be read).
            $create = (string)($connection->fetchRow('SHOW CREATE TABLE ' . $connection->quoteIdentifier($table))['Create Table'] ?? '');
            $nextId = preg_match('/AUTO_INCREMENT=(\d+)/', $create, $m)
                ? (int)$m[1]
                : (int)$connection->fetchOne($connection->select()->from($table, ['MAX(sequence_value)'])) + 1;
            $start = (int)$row['start_value'];
            $value = ($nextId - $start) * (int)$row['step'] + $start;
            // Same pattern as Magento\SalesSequence\Model\Sequence::DEFAULT_PATTERN.
            return sprintf("%s%'.09d%s", (string)$row['prefix'], $value, (string)$row['suffix']);
        } catch (\Throwable) {
            return null;
        }
    }

    public function lastNumber(string $entityType, int $storeId): ?string
    {
        if (!isset(self::TABLES[$entityType])) {
            return null;
        }
        $connection = $this->resource->getConnection('sales');
        $value = $connection->fetchOne(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLES[$entityType]), ['increment_id'])
                ->where('store_id = ?', $storeId)
                ->order('entity_id DESC')
                ->limit(1)
        );
        return $value === false ? null : (string)$value;
    }

    public function exists(string $entityType, string $number): bool
    {
        $connection = $this->resource->getConnection('sales');
        return (bool)$connection->fetchOne(
            $connection->select()
                ->from($this->resource->getTableName(self::TABLES[$entityType]), ['entity_id'])
                ->where('increment_id = ?', $number)
                ->limit(1)
        );
    }

    private function assertUsable(Settings $settings): void
    {
        if (!isset(self::TABLES[$settings->entityType])
            || !$this->formatter->isValidAffix($settings->prefix)
            || !$this->formatter->isValidAffix($settings->suffix)
        ) {
            throw new LocalizedException(__('The %1 number settings are not valid.', $settings->entityType));
        }
    }
}
