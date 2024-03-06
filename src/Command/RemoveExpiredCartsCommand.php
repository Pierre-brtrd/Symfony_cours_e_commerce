<?php

namespace App\Command;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:remove-expired-carts',
    description: 'Remove expired carts from the database.',
    aliases: ['a:c', 'app:remove-cart'],
)]
class RemoveExpiredCartsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private OrderRepository $orderRepo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'days',
                InputArgument::OPTIONAL,
                'The number of days a cart can remain inactive',
                2
            )
            ->addArgument(
                'limit',
                InputArgument::OPTIONAL,
                'The maximum number of carts to remove',
                10
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $days = $input->getArgument('days');
        $limit = $input->getArgument('limit');

        if ($days <= 0) {
            $io->error('The number of days should be greater than 0.');
            return Command::FAILURE;
        }

        if ($limit <= 0) {
            $io->error('The limit should be greater than 0.');
            return Command::FAILURE;
        }

        $limitDate = new \DateTime("- $days days");
        $expiredCartCount = 0;

        while ($carts = $this->orderRepo->findCartsNotModifiedSince($limitDate, $limit)) {
            foreach ($carts as $cart) {
                $this->em->remove($cart);
            }

            $this->em->flush();
            $this->em->clear();

            $expiredCartCount += count($carts);
        }

        if ($expiredCartCount) {
            $io->success("$expiredCartCount cart(s) have been deleted.");
        } else {
            $io->info('No expired carts.');
        }

        return Command::SUCCESS;
    }
}
