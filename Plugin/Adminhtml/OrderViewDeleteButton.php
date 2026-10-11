<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Plugin\Adminhtml;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use CommerceGears\OrderTools\Model\Config;

/**
 * "Delete Permanently" button on the order page, shown only when deleting is allowed for the user and the order's
 * status. Sends a POST with the form key after a confirmation dialog (view/adminhtml/web/js/delete-order.js).
 */
class OrderViewDeleteButton
{
    public function __construct(
        private readonly Config $config,
        private readonly AuthorizationInterface $authorization,
        private readonly Escaper $escaper
    ) {
    }

    /**
     * @param View $subject
     * @param LayoutInterface $layout
     * @return null
     */
    public function beforeSetLayout(View $subject, $layout)
    {
        if (!$this->config->isDeleteEnabled() || !$this->authorization->isAllowed('CommerceGears_OrderTools::delete')) {
            return null;
        }
        $order = $subject->getOrder();
        if (!$order || !$order->getId() || !$this->config->isStatusDeletable((string)$order->getStatus())) {
            return null;
        }
        $subject->addButton(
            'sot_delete',
            [
                'label' => __('Delete Permanently'),
                'class' => 'delete',
                'data_attribute' => [
                    'mage-init' => [
                        'CommerceGears_OrderTools/js/delete-order' => [
                            'url' => $subject->getUrl('commercegears_ordertools/order/delete'),
                            'orderId' => (int)$order->getId(),
                            'title' => (string)__('Delete Order Permanently'),
                            'message' => $this->escaper->escapeHtml(__(
                                'Permanently delete order #%1 with its invoices, shipments and credit memos? This cannot be undone. Stock, refunds and reward points are not restored.',
                                $order->getIncrementId()
                            )),
                            'cancelLabel' => (string)__('Cancel'),
                            'confirmLabel' => (string)__('Delete Permanently'),
                        ],
                    ],
                ],
            ],
            0,
            -10
        );
        return null;
    }
}
