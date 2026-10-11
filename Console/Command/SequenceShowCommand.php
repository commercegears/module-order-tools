<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Console\Command;

use Magento\Store\Model\StoreManagerInterface;
use CommerceGears\OrderTools\Model\Config;
use CommerceGears\OrderTools\Model\Numbering\NumberGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Read-only: shows the last and the next document numbers per store view.
 */
class SequenceShowCommand extends Command
{
    public function __construct(
        private readonly Config $config,
        private readonly NumberGenerator $generator,
        private readonly StoreManagerInterface $storeManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('commercegears:order-tools:sequence:show')
            ->setDescription('Show the last and next order, invoice, shipment and credit memo numbers per store view')
            ->addOption('store', null, InputOption::VALUE_REQUIRED, 'Store view ID or code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $only = $input->getOption('store');
        $table = new Table($output);
        $table->setHeaders(['Store view', 'Document', 'Numbering', 'Last number', 'Next number']);
        $found = false;
        foreach ($this->storeManager->getStores() as $store) {
            if ($only !== null && (string)$store->getId() !== (string)$only && $store->getCode() !== $only) {
                continue;
            }
            $found = true;
            foreach (Config::ENTITY_TYPES as $entityType) {
                $settings = $this->config->getNumberSettings($entityType, (int)$store->getId());
                $next = match (true) {
                    $settings->isCustom() => (string)$this->generator->preview($settings),
                    $settings->isSameAsOrder() => 'same as the order number',
                    default => (string)$this->generator->magentoNextNumber($entityType, (int)$store->getId()) . ' (Magento)',
                };
                $table->addRow([
                    $store->getCode() . ' (' . $store->getId() . ')',
                    $entityType,
                    $settings->mode,
                    (string)$this->generator->lastNumber($entityType, (int)$store->getId()),
                    $next,
                ]);
            }
        }
        if (!$found) {
            $output->writeln('<error>Store view not found.</error>');
            return Command::FAILURE;
        }
        $table->render();
        return Command::SUCCESS;
    }
}
