<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\ResourceModel\DeletedOrder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Softaware\OrderTools\Model\DeletedOrder;
use Softaware\OrderTools\Model\ResourceModel\DeletedOrder as Resource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'log_id';

    protected function _construct()
    {
        $this->_init(DeletedOrder::class, Resource::class);
    }
}
