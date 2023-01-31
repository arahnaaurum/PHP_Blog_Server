<?php

namespace App\Console;

use App\Blog\Http\ErrorResponse;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepositoryInterface;
use App\User\Entities\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUser extends Command
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // Имя команды
            ->setName('users:create')
            // Описание команды
            ->setDescription('Creates new user')
            // Аргументы
            ->addArgument('email',InputArgument::REQUIRED,'Email')
            ->addArgument('password',InputArgument::REQUIRED,'Password')
            ->addArgument('first_name',InputArgument::REQUIRED,'First name')
            ->addArgument('last_name',InputArgument::REQUIRED,'Last name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Create user command started');
        $email = $input->getArgument('email');

        if ($this->userExists($email)) {
            $output->writeln('User already exists: $email');
            return Command::FAILURE;
        }

        try {
            $this->userRepository->save(User::createFrom
            (
                $input->getArgument('email'),
                $input->getArgument('first_name'),
                $input->getArgument('last_name'),
                $input->getArgument('password'),
                $author = null
            ));
        } catch (\Exception $exception){
            $output->writeln($exception->getMessage());
            return Command::FAILURE;
        }

        $output->writeln("User created with email " .  $input->getArgument('email'),);
        return Command::SUCCESS;
    }

    private function userExists(string $email): bool
    {
        try {
            $this->userRepository->getByEmail($email);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }

}