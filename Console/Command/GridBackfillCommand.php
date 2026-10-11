<?php
/**
 * CommerceGears Order Tools
 *
 * @copyright Copyright (c) CommerceGears (https://www.commercegearscommerce.co.uk/)
 */
declare(strict_types=1);

namespace CommerceGears\OrderTools\Console\Command;

use CommerceGears\OrderTools\Model\Grid\Backfill;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GridBackfillCommand extends Command
{
    public function __construct(
        private readonly Backfill $backfill
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('commercegears:order-tools:grid:backfill')
            ->setDescription('Fill the Order Tools columns of the order grid for existing orders (in batches)')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Orders per batch', '500');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int)$input->getOption('batch-size');
        if ($batchSize < 1) {
            $output->writeln('<error>--batch-size must be a positive number.</error>');
            return Command::FAILURE;
        }
        $done = $this->backfill->run($batchSize, static function (int $done) use ($output): void {
            $output->writeln(sprintf('%d orders done', $done), OutputInterface::VERBOSITY_VERBOSE);
        });
        $output->writeln(sprintf('<info>Order grid columns filled for %d orders.</info>', $done));
        return Command::SUCCESS;
    }
}
