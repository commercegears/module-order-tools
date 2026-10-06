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
use Softaware\OrderTools\Model\DeleteMessages;
use Softaware\OrderTools\Model\OrderDeleter;

/**
 * "Delete Permanently" button on the order page. POST with form key only.
 */
class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Softaware_OrderTools::delete';

    public function __construct(
        Context $context,
        private readonly OrderDeleter $deleter,
        private readonly DeleteMessages $messages
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $orderId = (int)$this->getRequest()->getParam('order_id');
        $redirect = $this->resultRedirectFactory->create();
        try {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid form key. Please refresh the page.'));
            }
            $result = $this->deleter->delete([$orderId], $this->messages->getActor());
            $this->messages->addMessages($result);
            if ($result['deleted']) {
                return $redirect->setPath('sales/order/index');
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $orderId > 0
            ? $redirect->setPath('sales/order/view', ['order_id' => $orderId])
            : $redirect->setPath('sales/order/index');
    }
}
