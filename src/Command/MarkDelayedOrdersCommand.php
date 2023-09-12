<?php

namespace App\Command;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:mark-delayed-orders',
    description: 'Move all orders for which the estimated delivery date has passed to DELAYED status',
)]
class MarkDelayedOrdersCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $verbose = $input->getOption('verbose');

        $orders = $this->orderRepository->findNeedingToBeMarkedAsDelayed();
        $output->writeln("Marking ".count($orders)." orders as delayed");
        foreach($orders as $o) {
            $o->setStatus(OrderStatus::DELAYED);
            if($verbose) {
                $output->writeln(
                    "Processing order #".$o->getId(),
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }
        $this->entityManager->flush();
        $output->writeln("Complete");

        return Command::SUCCESS;
    }
}
