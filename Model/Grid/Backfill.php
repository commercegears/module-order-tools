<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\Grid;

use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Grid;

/**
 * Fills the sot_* order grid columns for existing orders, in batches (Magento's grid indexer keeps them up to date
 * for orders saved after installation).
 */
class Backfill
{
    public const COLUMNS = ['sot_items', 'sot_coupon_code', 'sot_shipping_country', 'sot_customer_note', 'sot_is_test'];

    public function __construct(
        private readonly Grid $orderGrid
    ) {
    }

    /**
     * @param callable|null $progress called with the number of orders done so far
     * @return int orders processed
     */
    public function run(int $batchSize = 500, ?callable $progress = null): int
    {
        $batchSize = max(1, min(5000, $batchSize));
        $connection = $this->orderGrid->getConnection();
        $gridTable = $this->orderGrid->getGridTable();
        $lastId = 0;
        $done = 0;
        do {
            $select = $this->gridOriginSelect()
                ->where('sales_order.entity_id > ?', $lastId)
                ->order('sales_order.entity_id ASC')
                ->limit($batchSize);
            $rows = $connection->fetchAll($select);
            if (!$rows) {
                break;
            }
            // Existing grid rows: only the module's columns are updated. Orders missing from the grid get a full row.
            $connection->insertOnDuplicate($gridTable, $rows, self::COLUMNS);
            $lastId = (int)end($rows)['entity_id'];
            $done += count($rows);
            if ($progress) {
                $progress($done);
            }
        } while (count($rows) === $batchSize);
        return $done;
    }

    /**
     * Grid::getGridOriginSelect() is public only from Magento 2.4.9 (protected in 2.4.7/2.4.8); a reflection call
     * works with both.
     */
    private function gridOriginSelect(): Select
    {
        return (new \ReflectionMethod($this->orderGrid, 'getGridOriginSelect'))->invoke($this->orderGrid);
    }
}
