<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Ui\Component\MassAction;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;
use CommerceGears\OrderTools\Model\Config;

/**
 * Order grid mass action that is hidden unless its feature is switched on and the admin user's role allows it.
 * (Hiding is for convenience; the controllers check the same again.)
 */
class GuardedAction extends Action
{
    public function __construct(
        ContextInterface $context,
        private readonly AuthorizationInterface $authorization,
        private readonly Config $config,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);
    }

    public function prepare()
    {
        parent::prepare();
        $feature = (string)($this->getData('feature') ?? '');
        $allowed = match ($feature) {
            'delete' => $this->config->isDeleteEnabled()
                && $this->authorization->isAllowed('CommerceGears_OrderTools::delete'),
            'test_flag' => $this->config->isTestFlagEnabled()
                && $this->authorization->isAllowed('CommerceGears_OrderTools::test_flag'),
            default => false,
        };
        if (!$allowed) {
            $config = (array)$this->getData('config');
            $config['actionDisable'] = true;
            $this->setData('config', $config);
        }
    }
}
