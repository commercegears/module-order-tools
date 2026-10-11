<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use CommerceGears\OrderTools\Model\Config;
use CommerceGears\OrderTools\Model\Source\OrderStatus;

/**
 * Deleted Orders Log page: whether deleting is switched on and which statuses may be deleted (for the empty state).
 */
class DeletedLog implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly OrderStatus $orderStatus
    ) {
    }

    public function isDeleteEnabled(): bool
    {
        return $this->config->isDeleteEnabled();
    }

    /**
     * Labels of the statuses that may be deleted, e.g. ["Canceled"].
     *
     * @return string[]
     */
    public function getDeletableStatusLabels(): array
    {
        $labels = [];
        foreach ($this->orderStatus->toOptionArray() as $option) {
            $labels[(string)$option['value']] = (string)$option['label'];
        }
        $result = [];
        foreach ($this->config->getDeletableStatuses() as $status) {
            $result[] = $labels[$status] ?? $status;
        }
        return $result;
    }
}
