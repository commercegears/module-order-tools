<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Controller\Adminhtml\Deleted;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use CommerceGears\OrderTools\Model\ResourceModel\DeletedOrder as DeletedOrderResource;

class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'CommerceGears_OrderTools::deleted_log';

    public function __construct(
        Context $context,
        private readonly DeletedOrderResource $resource
    ) {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        /** @var Page $page */
        $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if (!$this->hasEntries()) {
            $page->addHandle('commercegears_ordertools_deleted_index_empty');
        }
        $page->setActiveMenu('CommerceGears_OrderTools::deleted_log');
        $page->getConfig()->getTitle()->prepend(__('Deleted Orders Log'));
        return $page;
    }

    /**
     * While nothing was deleted the page explains the feature instead of showing an empty grid.
     */
    private function hasEntries(): bool
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getMainTable(), ['log_id'])
            ->limit(1);
        return (bool)$connection->fetchOne($select);
    }
}
