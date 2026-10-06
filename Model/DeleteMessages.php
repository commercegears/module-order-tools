<?php
/**
 * Softaware Order Tools
 *
 * @copyright Copyright (c) Softaware Commerce (https://www.softawarecommerce.co.uk/)
 */
declare(strict_types=1);

namespace Softaware\OrderTools\Model;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Message\ManagerInterface;

/**
 * Admin context (who, from where) and result messages for order deletion.
 */
class DeleteMessages
{
    private const MAX_LISTED = 20;

    public function __construct(
        private readonly Session $authSession,
        private readonly RemoteAddress $remoteAddress,
        private readonly ManagerInterface $messageManager,
        private readonly Config $config
    ) {
    }

    /**
     * @return array{user_id: ?int, username: ?string, ip: ?string}
     */
    public function getActor(): array
    {
        $user = $this->authSession->getUser();
        $ip = $this->remoteAddress->getRemoteAddress();
        return [
            'user_id' => $user ? (int)$user->getId() : null,
            'username' => $user ? (string)$user->getUserName() : null,
            'ip' => $ip ? (string)$ip : null,
        ];
    }

    /**
     * @param array{deleted: string[], refused: array<string, string>, failed: array<string, string>} $result
     */
    public function addMessages(array $result): void
    {
        if ($result['deleted']) {
            $this->messageManager->addSuccessMessage(__(
                'Deleted %1 order(s): %2',
                count($result['deleted']),
                $this->shortList($result['deleted'])
            ));
        }
        if ($result['refused']) {
            $items = [];
            foreach ($result['refused'] as $label => $status) {
                $items[] = $label . ' (' . $status . ')';
            }
            $this->messageManager->addErrorMessage(__(
                'Not deleted because of their status, %1 order(s): %2. Allowed statuses: %3.',
                count($items),
                $this->shortList($items),
                implode(', ', $this->config->getDeletableStatuses()) ?: (string)__('none')
            ));
        }
        foreach (array_slice($result['failed'], 0, self::MAX_LISTED, true) as $label => $message) {
            $this->messageManager->addErrorMessage(__('Order %1 was not deleted: %2', $label, $message));
        }
    }

    private function shortList(array $items): string
    {
        $list = implode(', ', array_slice($items, 0, self::MAX_LISTED));
        return count($items) > self::MAX_LISTED
            ? $list . ' ' . __('and %1 more', count($items) - self::MAX_LISTED)
            : $list;
    }
}
