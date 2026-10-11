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
