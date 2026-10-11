<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\ResourceModel\DeletedOrder;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use CommerceGears\OrderTools\Model\DeletedOrder;
use CommerceGears\OrderTools\Model\ResourceModel\DeletedOrder as Resource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'log_id';

    protected function _construct()
    {
        $this->_init(DeletedOrder::class, Resource::class);
    }
}
