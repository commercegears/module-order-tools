<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Api\Data\StoreInterface;
use Softaware\OrderTools\Model\Config;
use Softaware\OrderTools\Model\Numbering\NumberGenerator;

/**
 * Read-only table with the next number of each document type per store view (saved settings).
 */
class NumberPreview extends Field
{
    private const LABELS = [
        'order' => 'Order',
        'invoice' => 'Invoice',
        'shipment' => 'Shipment',
        'creditmemo' => 'Credit Memo',
    ];

    public function __construct(
        Context $context,
        private readonly Config $config,
        private readonly NumberGenerator $generator,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        return '<tr id="row_' . $this->_escaper->escapeHtmlAttr($element->getHtmlId()) . '"><td colspan="4">'
            . $this->_getElementHtml($element) . '</td></tr>';
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $escaper = $this->_escaper;
        $html = '<table class="data-grid sot-number-preview"><thead><tr>'
            . '<th class="data-grid-th">' . $escaper->escapeHtml(__('Store View')) . '</th>';
        foreach (self::LABELS as $label) {
            $html .= '<th class="data-grid-th">' . $escaper->escapeHtml(__($label)) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($this->getStores() as $store) {
            $html .= '<tr><td style="padding:.6rem 1rem">' . $escaper->escapeHtml($store->getName()) . '</td>';
            foreach (array_keys(self::LABELS) as $entityType) {
                $html .= '<td style="white-space:nowrap;padding:.6rem 1rem">'
                    . $this->renderCell($entityType, (int)$store->getId()) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    private function renderCell(string $entityType, int $storeId): string
    {
        $escaper = $this->_escaper;
        $settings = $this->config->getNumberSettings($entityType, $storeId);
        if ($settings->isSameAsOrder()) {
            return '<em>' . $escaper->escapeHtml(__('Same as the order number')) . '</em>';
        }
        try {
            $next = $this->generator->preview($settings);
        } catch (\Throwable $e) {
            return $escaper->escapeHtml(__('Not available'));
        }
        if ($next === null) {
            $last = $this->generator->lastNumber($entityType, $storeId);
            return '<em>' . $escaper->escapeHtml(__('Magento default')) . '</em>'
                . ($last !== null ? '<br/><small>' . $escaper->escapeHtml(__('last: %1', $last)) . '</small>' : '');
        }
        return '<strong>' . $escaper->escapeHtml($next) . '</strong>';
    }

    /**
     * Store views of the scope being edited.
     *
     * @return StoreInterface[]
     */
    private function getStores(): array
    {
        $storeParam = $this->getRequest()->getParam('store');
        $websiteParam = $this->getRequest()->getParam('website');
        $stores = [];
        foreach ($this->_storeManager->getStores() as $store) {
            if ($storeParam !== null && $storeParam !== '' && (int)$store->getId() !== (int)$storeParam) {
                continue;
            }
            if ($websiteParam !== null && $websiteParam !== '' && (int)$store->getWebsiteId() !== (int)$websiteParam) {
                continue;
            }
            $stores[] = $store;
        }
        return $stores;
    }
}
