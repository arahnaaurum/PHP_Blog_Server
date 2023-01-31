<?php

namespace App\Console;

use App\Blog\Http\ErrorResponse;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepositoryInterface;
use App\User\Entities\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUser extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('users:update')
            ->setDescription('Updates user info')
            ->addArgument('id',InputArgument::REQUIRED,'ID of the required user')
            ->addOption('first_name','f', InputOption::VALUE_OPTIONAL, 'First name')
            ->addOption('last_name','l', InputOption::VALUE_OPTIONAL, 'Last name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $firstName = $input->getOption('first_name');
        $lastName = $input->getOption('last_name');

        if (empty($firstName) && empty($lastName)) {
            $output->writeln('Nothing to update');
            return Command::SUCCESS;
        }

        $id = $input->getArgument('id');

        $this->userRepository->update($id, $firstName, $lastName);

        $output->writeln("User with id $id updated");
        return Command::SUCCESS;
    }
}