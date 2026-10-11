<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use CommerceGears\OrderTools\Model\Config;

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
