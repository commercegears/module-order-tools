<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Softaware\OrderTools\Model\Numbering\Settings;

/**
 * Typed access to the module settings.
 */
class Config
{
    public const ENTITY_TYPES = ['order', 'invoice', 'shipment', 'creditmemo'];

    public const MODE_DEFAULT = 'default';
    public const MODE_CUSTOM = 'custom';
    public const MODE_SAME_AS_ORDER = 'order';

    public const EMAIL_KEEP = 'keep';
    public const EMAIL_HASH = 'hash';

    private const XML_ENABLED = 'softaware_ordertools/general/enabled';
    private const XML_GRID_COLUMNS = 'softaware_ordertools/grid/columns';
    private const XML_TEST_FLAG = 'softaware_ordertools/grid/test_flag';
    private const XML_DELETE_ENABLED = 'softaware_ordertools/delete/enabled';
    private const XML_DELETE_STATUSES = 'softaware_ordertools/delete/statuses';
    private const XML_DELETE_EMAIL = 'softaware_ordertools/delete/customer_email';
    private const XML_NUMBERS = 'softaware_ordertools_numbers/';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_ENABLED);
    }

    /**
     * Extra order grid columns switched on in the settings (column names, e.g. sot_items).
     *
     * @return string[]
     */
    public function getGridColumns(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }
        return $this->splitList((string)$this->scopeConfig->getValue(self::XML_GRID_COLUMNS));
    }

    public function isTestFlagEnabled(): bool
    {
        return $this->isEnabled() && $this->scopeConfig->isSetFlag(self::XML_TEST_FLAG);
    }

    public function isDeleteEnabled(): bool
    {
        return $this->isEnabled() && $this->scopeConfig->isSetFlag(self::XML_DELETE_ENABLED);
    }

    /**
     * Order statuses that may be deleted. Empty list means nothing may be deleted.
     *
     * @return string[]
     */
    public function getDeletableStatuses(): array
    {
        return $this->splitList((string)$this->scopeConfig->getValue(self::XML_DELETE_STATUSES));
    }

    public function isStatusDeletable(?string $status): bool
    {
        return $status !== null && $status !== '' && in_array($status, $this->getDeletableStatuses(), true);
    }

    public function getEmailInLog(): string
    {
        $value = (string)$this->scopeConfig->getValue(self::XML_DELETE_EMAIL);
        return $value === self::EMAIL_KEEP ? self::EMAIL_KEEP : self::EMAIL_HASH;
    }

    /**
     * Numbering settings of one document type in one store view.
     */
    public function getNumberSettings(string $entityType, int $storeId): Settings
    {
        $get = fn (string $field) => (string)$this->scopeConfig->getValue(
            self::XML_NUMBERS . $entityType . '/' . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $mode = $get('mode');
        $allowed = $entityType === 'order'
            ? [self::MODE_CUSTOM]
            : [self::MODE_CUSTOM, self::MODE_SAME_AS_ORDER];
        if (!$this->isEnabled() || !in_array($entityType, self::ENTITY_TYPES, true) || !in_array($mode, $allowed, true)) {
            $mode = self::MODE_DEFAULT;
        }
        return new Settings(
            entityType: $entityType,
            storeId: $storeId,
            mode: $mode,
            prefix: trim($get('prefix')),
            suffix: trim($get('suffix')),
            length: max(1, min(Settings::MAX_LENGTH, (int)$get('length'))),
            start: max(0, (int)$get('start')),
            step: max(1, (int)$get('step')),
            // Global on purpose: store views with the same prefix and suffix share one counter, so they must
            // also share its reset rule.
            reset: (string)$this->scopeConfig->getValue(self::XML_NUMBERS . $entityType . '/reset')
                ?: Settings::RESET_NEVER
        );
    }

    /**
     * @return string[]
     */
    private function splitList(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'));
    }
}
