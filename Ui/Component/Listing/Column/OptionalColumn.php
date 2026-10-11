<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use CommerceGears\OrderTools\Model\Config;

/**
 * Order grid column that is only there when switched on in the settings (the test order column with the test
 * order flag, the others in "Extra Columns").
 */
class OptionalColumn extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly Config $config,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare()
    {
        parent::prepare();
        $name = (string)$this->getName();
        $enabled = $name === 'sot_is_test'
            ? $this->config->isTestFlagEnabled()
            : in_array($name, $this->config->getGridColumns(), true);
        if (!$enabled) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
