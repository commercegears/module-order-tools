<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Extra order grid columns (sales_order_grid.sot_*).
 */
class GridColumns implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'sot_items', 'label' => __('Items (SKU x quantity)')],
            ['value' => 'sot_coupon_code', 'label' => __('Coupon Code')],
            ['value' => 'sot_shipping_country', 'label' => __('Shipping Country')],
            ['value' => 'sot_customer_note', 'label' => __('Order Comment (customer note)')],
        ];
    }
}
