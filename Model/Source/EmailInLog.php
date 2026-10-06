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

class EmailInLog implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::EMAIL_HASH, 'label' => __('Store a hash (SHA-256) only')],
            ['value' => Config::EMAIL_KEEP, 'label' => __('Store the email address')],
        ];
    }
}
