<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use CommerceGears\OrderTools\Model\Config;

class OrderNumberMode implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::MODE_DEFAULT, 'label' => __('Magento default')],
            ['value' => Config::MODE_CUSTOM, 'label' => __('Custom format')],
        ];
    }
}
