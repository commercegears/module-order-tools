<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\Grid;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;

/**
 * Order grid column "Items": "SKU x qty, SKU x qty" of the order's top-level items, built by Magento's order grid
 * indexer (synchronous and asynchronous) from the order being indexed (alias sales_order).
 * Fixed SQL only; no user input is part of the expression.
 */
class ItemsExpression extends Expression
{
    /**
     * The expression is built in __toString(), when the table prefix is known.
     */
    public function __construct(
        private readonly ResourceConnection $resource
    ) {
    }

    public function __toString(): string
    {
        $connection = $this->resource->getConnection('sales');
        $table = $connection->quoteIdentifier($this->resource->getTableName('sales_order_item'));
        $qty = "TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM sot_item.qty_ordered))";
        return '(SELECT GROUP_CONCAT(CONCAT(sot_item.sku, \' x \', ' . $qty . ')'
            . ' ORDER BY sot_item.item_id SEPARATOR \', \')'
            . ' FROM ' . $table . ' AS sot_item'
            . ' WHERE sot_item.order_id = sales_order.entity_id AND sot_item.parent_item_id IS NULL)';
    }
}
