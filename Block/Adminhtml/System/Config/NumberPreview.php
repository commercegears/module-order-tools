<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Store\Api\Data\StoreInterface;
use CommerceGears\OrderTools\Model\Config;
use CommerceGears\OrderTools\Model\Numbering\NumberGenerator;

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
        // The stylesheet is linked here, so it is only loaded on this settings section (not on every config page).
        $html = '<link rel="stylesheet" type="text/css" href="'
            . $escaper->escapeUrl($this->getViewFileUrl('CommerceGears_OrderTools::css/admin.css')) . '"/>';
        if (!$this->config->isEnabled()) {
            $html .= '<div class="message message-warning sot-number-preview__notice"><div>'
                . $escaper->escapeHtml(__(
                    'Order Tools is switched off (Order Tools > General > Enabled), so Magento\'s own numbers are used.'
                ))
                . '</div></div>';
        }
        $html .= '<div class="sot-number-preview__wrap"><table class="data-grid sot-number-preview" aria-label="'
            . $escaper->escapeHtmlAttr(__('Next Numbers')) . '"><thead><tr><th class="data-grid-th" scope="col">' . $escaper->escapeHtml(__('Store View')) . '</th>';
        foreach (self::LABELS as $label) {
            $html .= '<th class="data-grid-th" scope="col">' . $escaper->escapeHtml(__($label)) . '</th>';
        }
        $html .= '</tr></thead><tbody>';
        foreach ($this->getStores() as $store) {
            $html .= '<tr><th scope="row">' . $escaper->escapeHtml($store->getName()) . '</th>';
            foreach (array_keys(self::LABELS) as $entityType) {
                $html .= '<td>' . $this->renderCell($entityType, (int)$store->getId()) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        return $html;
    }

    private function renderCell(string $entityType, int $storeId): string
    {
        $escaper = $this->_escaper;
        $settings = $this->config->getNumberSettings($entityType, $storeId);
        if ($settings->isSameAsOrder()) {
            return '<span class="sot-number-preview__muted">' . $escaper->escapeHtml(__('Same as the order number')) . '</span>';
        }
        try {
            $next = $this->generator->preview($settings);
        } catch (\Throwable $e) {
            return $escaper->escapeHtml(__('Not available'));
        }
        if ($next === null) {
            // Magento's own numbering: show the number its sequence gives next.
            $magentoNext = $this->generator->magentoNextNumber($entityType, $storeId);
            return ($magentoNext !== null
                    ? '<strong class="sot-number-preview__next">' . $escaper->escapeHtml($magentoNext) . '</strong>'
                    : '')
                . '<span class="sot-number-preview__note">' . $escaper->escapeHtml(__('Magento numbering')) . '</span>';
        }
        return '<strong class="sot-number-preview__next">' . $escaper->escapeHtml($next) . '</strong>'
            . '<span class="sot-number-preview__note">' . $escaper->escapeHtml(__('Custom format')) . '</span>';
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
