<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Softaware\OrderTools\Model\Source\OrderStatus as OrderStatusSource;

/**
 * Deleted orders log: shows the status label (e.g. "Canceled") instead of its code. A status that no longer exists
 * is shown by its code. The filter and the CSV export use the code.
 */
class OrderStatus extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly OrderStatusSource $statusSource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }
        $labels = [];
        foreach ($this->statusSource->toOptionArray() as $option) {
            $labels[(string)$option['value']] = (string)$option['label'];
        }
        $name = (string)$this->getData('name');
        foreach ($dataSource['data']['items'] as $i => $item) {
            $code = (string)($item[$name] ?? '');
            if (isset($labels[$code])) {
                $dataSource['data']['items'][$i][$name] = $labels[$code];
            }
        }
        return $dataSource;
    }
}
