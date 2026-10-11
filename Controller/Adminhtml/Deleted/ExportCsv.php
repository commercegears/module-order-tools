<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Controller\Adminhtml\Deleted;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Ui\Model\Export\ConvertToCsv;

/**
 * CSV export of the deleted orders log (filters of the grid apply). Read-only. Only the deleted orders listing can
 * be exported here, and values that a spreadsheet would run as a formula are prefixed with an apostrophe.
 */
class ExportCsv extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'CommerceGears_OrderTools::deleted_log';
    public const NAMESPACE = 'commercegears_ordertools_deleted_listing';

    public function __construct(
        Context $context,
        private readonly ConvertToCsv $converter,
        private readonly FileFactory $fileFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): ResponseInterface
    {
        if ($this->getRequest()->getParam('namespace') !== self::NAMESPACE) {
            throw new NotFoundException(__('Page not found.'));
        }
        return $this->fileFactory->create(
            'deleted-orders-' . date('Ymd-His') . '.csv',
            $this->converter->getCsvFile(),
            DirectoryList::VAR_DIR
        );
    }
}
