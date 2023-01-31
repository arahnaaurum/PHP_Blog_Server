<?php

namespace App\Console;

use App\Exceptions\PostNotFoundException;
use App\Repositories\PostRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeletePost extends Command
{
    public function __construct(private PostRepositoryInterface $postRepository,)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('posts:delete')
            ->setDescription('Deletes a post')
            ->addArgument(
                'id',
                InputArgument::REQUIRED,
                'ID of a post to delete'
            )
            ->addOption(
                'check-existence',
                'c',
                InputOption::VALUE_NONE,
                'Check if post actually exists',
            );
    }

    protected function execute(
        InputInterface  $input,
        OutputInterface $output,
    ): int {
        $question = new ConfirmationQuestion(
            'Delete post [Y/n]? ',
            false
        );
        if (!$this->getHelper('question')
            ->ask($input, $output, $question)
        ) {
            return Command::SUCCESS;
        }

        $id = $input->getArgument('id');

        if ($input->getOption('check-existence')) {
            try {
                $this->postRepository->get($id);
            } catch (PostNotFoundException $e) {
                $output->writeln($e->getMessage());
                return Command::FAILURE;
            }
        }

        $this->postRepository->delete($id);
        $output->writeln("Post with id: $id deleted");
        return Command::SUCCESS;
    }

}