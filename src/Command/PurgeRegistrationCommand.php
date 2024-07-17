<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'purge-registration',
    description: 'Add a short description for your command',
    aliases: ['app:purge-registration'],
    hidden: false
)]
class PurgeRegistrationCommand extends Command
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $repository)
    {
        parent::__construct();

        $this->userRepository = $repository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('days', null, InputOption::VALUE_OPTIONAL, 'Number of days for unverified users', null)
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete unverified users')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Delete unverified users without confirmation')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = $input->getOption('days');
        $delete = $input->getOption('delete');
        $force = $input->getOption('force');

        if (null !== $days && !ctype_digit($days)) {
            throw new \Exception('L\'option --days doit être un nombre entier positif.');
        }

        $unverifiedUsers = $this->userRepository->findUnverifiedUsersSince(null !== $days ? $days : 0);

        $dateNow = new \DateTimeImmutable();

        $table = new Table($output);
        $table->setHeaders(['Nom', 'Prénom', 'EMAIL', 'Jours écoulés']);

        foreach ($unverifiedUsers as $user) {
            $table->addRow([
                $user->getLastname(),
                $user->getFirstname(),
                $user->getEmail(),
                $user->getRegisteredAt()->diff($dateNow)->days,
            ]);
        }

        $table->render();

        if ($delete) {
            if ($force) {
                $this->userRepository->deleteUnverifiedUsersSince(null !== $days ? $days : 0);
                $output->writeln('Opération réussie, '.count($unverifiedUsers).' utilisateur(s) supprimé(s)');
            } else {
                $confirmation = readline('Êtes-vous sûr de vouloir supprimer ces utilisateurs ? (oui/non) ');
                if ('oui' !== strtolower($confirmation)) {
                    $output->writeln('Opération annulée.');

                    return Command::SUCCESS;
                } else {
                    $this->userRepository->deleteUnverifiedUsersSince(null !== $days ? $days : 0);
                    $output->writeln('Opération réussie, '.count($unverifiedUsers).' utilisateur(s) supprimé(s)');
                }
            }
        }

        return Command::SUCCESS;
    }
}
