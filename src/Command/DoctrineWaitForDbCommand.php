<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'doctrine:wait-for-db',
    description: 'Wait for the DB to finish initialising on first run',
)]
class DoctrineWaitForDbCommand extends Command
{
    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sm = $this->connection->createSchemaManager();
        $start = (new \DateTimeImmutable())->getTimestamp();
        $timeElapsed = 0;

        while($timeElapsed < 30) {
            $timeElapsed = (new \DateTimeImmutable())->getTimestamp() - $start;

            try {
                $sm->listTables();
                $output->writeln("DB ready");
                return Command::SUCCESS;
            } catch (ConnectionException) {
                $output->writeln("Waiting for DB to initialise...");
                sleep(3);
            }
        }

        $output->writeln("Timed out waiting for DB to initialise");
        return Command::FAILURE;
    }
}
