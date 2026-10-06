<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Softaware\OrderTools\Model\Numbering\Settings;

class CounterReset implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => Settings::RESET_NEVER, 'label' => __('Never')],
            ['value' => Settings::RESET_YEARLY, 'label' => __('Every year')],
            ['value' => Settings::RESET_MONTHLY, 'label' => __('Every month')],
        ];
    }
}
