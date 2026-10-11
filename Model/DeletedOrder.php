<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model;

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
