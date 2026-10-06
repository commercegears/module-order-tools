<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Softaware\OrderTools\Model\Config;

class DocumentNumberMode implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::MODE_DEFAULT, 'label' => __('Magento default')],
            ['value' => Config::MODE_CUSTOM, 'label' => __('Custom format')],
            ['value' => Config::MODE_SAME_AS_ORDER, 'label' => __('Same as the order number')],
        ];
    }
}
