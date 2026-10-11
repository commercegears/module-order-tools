<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://commercegears.com/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Model\Numbering;

use Magento\Framework\DB\Sequence\SequenceInterface;
use Psr\Log\LoggerInterface;

/**
 * Sequence that hands out custom numbers and falls back to Magento's sequence if that fails, so an order or
 * invoice is never lost because of the numbering.
 */
class CustomSequence implements SequenceInterface
{
    private ?string $current = null;

    public function __construct(
        private readonly Settings $settings,
        private readonly SequenceInterface $fallback,
        private readonly NumberGenerator $generator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getNextValue()
    {
        try {
            $this->current = $this->generator->next($this->settings);
        } catch (\Throwable $e) {
            $this->logger->error(
                'CommerceGears_OrderTools: custom ' . $this->settings->entityType . ' number failed, Magento number used: '
                . $e->getMessage()
            );
            $this->current = (string)$this->fallback->getNextValue();
        }
        return $this->current;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentValue()
    {
        return $this->current;
    }
}
