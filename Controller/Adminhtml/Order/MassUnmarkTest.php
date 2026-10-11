<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use CommerceGears\OrderTools\Model\Config;
use CommerceGears\OrderTools\Model\OrderDeleter;
use CommerceGears\OrderTools\Model\TestFlag;

class MassUnmarkTest extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'CommerceGears_OrderTools::test_flag';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly TestFlag $testFlag,
        private readonly Config $config
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        try {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid form key. Please refresh the page.'));
            }
            if (!$this->config->isTestFlagEnabled()) {
                throw new LocalizedException(__('The test order flag is switched off in the Order Tools settings.'));
            }
            if ($this->getRequest()->getParam('namespace') !== 'sales_order_grid') {
                throw new LocalizedException(__('Use the order grid to mark test orders.'));
            }
            $ids = $this->filter->getCollection($this->collectionFactory->create())->getAllIds();
            if (count($ids) > OrderDeleter::MAX_BATCH) {
                throw new LocalizedException(
                    __('You selected %1 orders. Select at most %2 orders at a time.', count($ids), OrderDeleter::MAX_BATCH)
                );
            }
            $this->testFlag->set($ids, false);
            $this->messageManager->addSuccessMessage(__('Removed the test order mark from %1 order(s).', count($ids)));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath('sales/order/index');
    }
}
