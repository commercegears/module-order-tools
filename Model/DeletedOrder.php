<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Entry of the deleted orders log (read-only in the admin).
 */
class DeletedOrder extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\DeletedOrder::class);
    }
}
