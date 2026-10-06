<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DeletedOrder extends AbstractDb
{
    public const TABLE = 'softaware_ordertools_deleted_order';

    protected $connectionName = 'sales';

    protected function _construct()
    {
        $this->_init(self::TABLE, 'log_id');
    }
}
