<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\ResourceModel\GridInterface;

/**
 * Marks orders as test orders (sales_order.sot_is_test) and refreshes their order grid rows.
 */
class TestFlag
{
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly GridInterface $orderGrid
    ) {
    }

    /**
     * @param int[] $orderIds
     * @return int number of orders changed
     */
    public function set(array $orderIds, bool $isTest): int
    {
        $orderIds = array_values(array_unique(array_filter(array_map('intval', $orderIds))));
        if (!$orderIds) {
            return 0;
        }
        $connection = $this->resource->getConnection('sales');
        $changed = $connection->update(
            $this->resource->getTableName('sales_order'),
            ['sot_is_test' => $isTest ? 1 : 0],
            ['entity_id IN (?)' => $orderIds]
        );
        foreach ($orderIds as $orderId) {
            $this->orderGrid->refresh($orderId);
        }
        return (int)$changed;
    }
}
