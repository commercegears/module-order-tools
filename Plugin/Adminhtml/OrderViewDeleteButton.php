<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Plugin\Adminhtml;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Softaware\OrderTools\Model\Config;

/**
 * "Delete Permanently" button on the order page, shown only when deleting is allowed for the user and the order's
 * status. Sends a POST with the form key after a confirmation.
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
        if (!$this->config->isDeleteEnabled() || !$this->authorization->isAllowed('Softaware_OrderTools::delete')) {
            return null;
        }
        $order = $subject->getOrder();
        if (!$order || !$order->getId() || !$this->config->isStatusDeletable((string)$order->getStatus())) {
            return null;
        }
        $message = $this->escaper->escapeJs($this->escaper->escapeHtml(__(
            'Permanently delete order #%1 with its invoices, shipments and credit memos? This cannot be undone. Stock, refunds and reward points are not restored.',
            $order->getIncrementId()
        )));
        $url = $this->escaper->escapeJs($subject->getUrl('softaware_ordertools/order/delete'));
        $data = '{data: {order_id: ' . (int)$order->getId() . '}}';
        $subject->addButton(
            'sot_delete',
            [
                'label' => __('Delete Permanently'),
                'class' => 'delete',
                'onclick' => "deleteConfirm('{$message}', '{$url}', {$data})",
            ],
            0,
            -10
        );
        return null;
    }
}
