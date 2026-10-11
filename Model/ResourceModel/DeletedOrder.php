<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DeletedOrder extends AbstractDb
{
    public const TABLE = 'commercegears_ordertools_deleted_order';

    protected $connectionName = 'sales';

    protected function _construct()
    {
        $this->_init(self::TABLE, 'log_id');
    }
}
