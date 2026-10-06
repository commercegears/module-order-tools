<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Softaware\OrderTools\Model\DeleteMessages;
use Softaware\OrderTools\Model\OrderDeleter;

/**
 * "Delete Permanently" mass action of the order grid. POST with form key only (checked by Magento's backend
 * request validator for POST requests); the status guard and batch limit are enforced by OrderDeleter.
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Softaware_OrderTools::delete';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly OrderDeleter $deleter,
        private readonly DeleteMessages $messages
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $redirect = $this->resultRedirectFactory->create()->setPath('sales/order/index');
        try {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid form key. Please refresh the page.'));
            }
            if ($this->getRequest()->getParam('namespace') !== 'sales_order_grid') {
                throw new LocalizedException(__('Use the order grid to delete orders.'));
            }
            $ids = $this->filter->getCollection($this->collectionFactory->create())->getAllIds();
            $this->messages->addMessages($this->deleter->delete($ids, $this->messages->getActor()));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $redirect;
    }
}
